<?php

/*
 * This file is part of the mangel.io project.
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
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Issue;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;
use Symfony\Component\Intl\Exception\NotImplementedException;

class IssueVoter extends ConstructionSiteOwnedEntityVoter
{
    public const ISSUE_VIEW = 'ISSUE_VIEW';
    public const ISSUE_MODIFY = 'ISSUE_MODIFY';
    public const ISSUE_RESPOND = 'ISSUE_RESPOND';

    protected function isExpectedConstructionSiteOwnedEntityInstance(ConstructionSiteOwnedEntityInterface $constructionSiteOwnedEntity): bool
    {
        return $constructionSiteOwnedEntity instanceof Issue;
    }

    protected function getAttributes(): array
    {
        return [self::ISSUE_VIEW, self::ISSUE_RESPOND, self::ISSUE_MODIFY];
    }

    protected function getConstructionManagerAccessibleAttributes(ConstructionManager $manager): array
    {
        return [self::ISSUE_VIEW, self::ISSUE_RESPOND, self::ISSUE_MODIFY];
    }

    protected function getCraftsmanAccessibleAttributes(Craftsman $craftsman): array
    {
        return [self::ISSUE_VIEW, self::ISSUE_RESPOND];
    }

    protected function getFilterAccessibleAttributes(Filter $filter): array
    {
        return [self::ISSUE_VIEW];
    }

    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        throw new NotImplementedException('need to filter issue by filter');
    }
}
