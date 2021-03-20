<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Service\Issue\Summary;
use App\Service\Issue\SummaryWithDate;
use Doctrine\ORM\QueryBuilder;

interface IssueServiceInterface
{
    /**
     * @return SummaryWithDate[]
     */
    public function createTimeseries(string $rootAlias, QueryBuilder $queryBuilder, \DateTime $lastPeriodEnd, \DateInterval $stepSize, int $stepCount): array;

    public function createDeltaSummaries(Summary $summary, array $stateChangeIssues, \DateTime $lastPeriodEnd, \DateInterval $stepSize = null, int $stepCount = 0): array;

    public function createSummary(string $rootAlias, QueryBuilder $queryBuilder): Summary;
}
