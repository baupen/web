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
use App\Entity\Filter;
use App\Security\Voter\Base\ConstructionSiteRelatedEntityVoter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConstructionManagerVoter extends ConstructionSiteRelatedEntityVoter
{
    public const CONSTRUCTION_MANAGER_VIEW = 'CONSTRUCTION_MANAGER_VIEW';
    public const CONSTRUCTION_MANAGER_SELF = 'CONSTRUCTION_MANAGER_SELF';

    public function __construct(private readonly ManagerRegistry $registry)
    {
    }

    /**
     * @param ConstructionManager $subject
     */
    protected function isConstructionManagerRelated(ConstructionManager $constructionManager, $subject): bool
    {
        if ($constructionManager === $subject) {
            return true;
        }

        $constructionManagerRepository = $this->registry->getRepository(ConstructionManager::class);
        if ($constructionManagerRepository->checkConstructionManagerRelated($subject, $constructionManager->getConstructionSites())) {
            return true;
        }

        return false;
    }

    protected function isCraftsmanRelated(Craftsman $craftsman, $subject)
    {
        return $craftsman->getConstructionSite()->getConstructionManagers()->contains($subject);
    }

    protected function isFilterRelated(Filter $filter, $subject)
    {
        return $filter->getConstructionSite()->getConstructionManagers()->contains($subject);
    }

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof ConstructionManager;
    }

    protected function getAllAttributes(): array
    {
        return [self::CONSTRUCTION_MANAGER_VIEW, self::CONSTRUCTION_MANAGER_SELF];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::CONSTRUCTION_MANAGER_VIEW];
    }

    protected function getDissociatedConstructionManagerAttributes(bool $canAssociateSelf): array
    {
        if (!$canAssociateSelf) {
            return [];
        }

        return [self::CONSTRUCTION_MANAGER_VIEW];
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (self::CONSTRUCTION_MANAGER_SELF === $attribute) {
            $constructionManager = $this->tryGetConstructionManager($token);

            return null !== $subject && $subject === $constructionManager;
        }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }
}
