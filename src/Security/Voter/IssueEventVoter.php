<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\IssueEvent;
use App\Enum\IssueEventTypes;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IssueEventVoter extends ConstructionSiteOwnedEntityVoter
{
    public const ISSUE_EVENT_VIEW = 'ISSUE_EVENT_VIEW';
    public const ISSUE_EVENT_CREATE = 'ISSUE_EVENT_CREATE';
    public const ISSUE_EVENT_MODIFY = 'ISSUE_EVENT_MODIFY';
    public const ISSUE_EVENT_DELETE = 'ISSUE_EVENT_DELETE';

    public function __construct(private readonly ManagerRegistry $registry)
    {
    }

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof IssueEvent;
    }

    protected function getAllAttributes(): array
    {
        return [self::ISSUE_EVENT_VIEW, self::ISSUE_EVENT_CREATE, self::ISSUE_EVENT_MODIFY, self::ISSUE_EVENT_DELETE];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::ISSUE_EVENT_VIEW];
    }

    protected function getModifyAttributes(): array
    {
        return [self::ISSUE_EVENT_CREATE, self::ISSUE_EVENT_MODIFY, self::ISSUE_EVENT_DELETE];
    }

    protected function getRelatedCraftsmanAccessibleAttributes(): array
    {
        return $this->getModifyAttributes();
    }

    /**
     * @param IssueEvent $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        // disable editing of read-only events
        if (self::ISSUE_EVENT_VIEW !== $attribute && !IssueEventTypes::isManualEvent($subject->getType())) {
            return false;
        }

        $craftsman = $this->tryGetCraftsman($token);
        if ($craftsman instanceof Craftsman) {
            return $this->checkAccessCraftsman($subject, $craftsman, $attribute) && parent::voteOnAttribute($attribute, $subject, $token);
        }

        $constructionManager = $this->tryGetConstructionManager($token);
        if ($constructionManager instanceof ConstructionManager) {
            return $this->checkAccessConstructionManager($subject, $constructionManager, $attribute) && parent::voteOnAttribute($attribute, $subject, $token);
        }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }

    private function checkAccessCraftsman(IssueEvent $subject, Craftsman $craftsman, string $attribute): bool
    {
        // access only events of issues assigned to
        $issueRepository = $this->registry->getRepository(Issue::class);
        $issue = $issueRepository->find($subject->getRoot());
        if (!$issue instanceof Issue || $issue->getCraftsman() !== $craftsman) {
            return false;
        }

        // access only own
        if ($subject->getCreatedBy() !== $craftsman->getId()) {
            return false;
        }

        if (in_array($attribute, [self::ISSUE_EVENT_CREATE, self::ISSUE_EVENT_MODIFY, self::ISSUE_EVENT_DELETE], true)) {
            if (!$craftsman->getCanEdit()) {
                return false;
            }

            // allow only access to open issue log
            if (!$issue->getRegisteredAt() || $issue->getResolvedAt() || $issue->getClosedAt()) {
                return false;
            }

            if (in_array($attribute, [self::ISSUE_EVENT_CREATE, self::ISSUE_EVENT_MODIFY], true)) {
                // must mark self as modifier
                if ($subject->getLastChangedBy() !== $craftsman->getId()) {
                    return false;
                }

                // must use correct time
                if (self::ISSUE_EVENT_CREATE === $attribute) {
                    if (!$subject->isConstructionSiteSet()) {
                        return false;
                    }

                    // must use correct construction site
                    if ($issue->getConstructionSite() !== $subject->getConstructionSite()) {
                        return false;
                    }

                    // must use valid event type
                    if (!IssueEventTypes::isManualEvent($subject->getType())) {
                        return false;
                    }

                    if ($subject->getTimestamp() > new \DateTime('now + 5min') || $subject->getTimestamp() < new \DateTime('now - 5min')) {
                        return false;
                    }
                } else {
                    // must not modify time
                    $diff = $subject->getCreatedAt()->diff($subject->getTimestamp());
                    if ($diff > new \DateInterval('PT5M')) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function checkAccessConstructionManager(IssueEvent $subject, ConstructionManager $constructionManager, string $attribute): bool
    {
        if (in_array($attribute, [self::ISSUE_EVENT_CREATE, self::ISSUE_EVENT_MODIFY, self::ISSUE_EVENT_DELETE], true)) {
            // must use valid event type
            if (!IssueEventTypes::isManualEvent($subject->getType())) {
                return false;
            }

            if (in_array($attribute, [self::ISSUE_EVENT_CREATE, self::ISSUE_EVENT_MODIFY], true)) {
                // must mark self as modifier
                if ($subject->getLastChangedBy() !== $constructionManager->getId()) {
                    return false;
                }

                if (self::ISSUE_EVENT_CREATE === $attribute) {
                    // must mark self as creator
                    if ($subject->getCreatedBy() !== $constructionManager->getId()) {
                        return false;
                    }

                    if (!$subject->isConstructionSiteSet()) {
                        return false;
                    }

                    // must reference valid root belonging to construction site
                    if ($subject->getRoot() !== $subject->getConstructionSite()->getId()
                        && !$this->registry->getRepository(Issue::class)
                            ->findOneBy(['id' => $subject->getRoot(), 'constructionSite' => $subject->getConstructionSite()->getId()])
                        && !$this->registry->getRepository(Craftsman::class)
                            ->findOneBy(['id' => $subject->getRoot(), 'constructionSite' => $subject->getConstructionSite()->getId()])
                    ) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
