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

use App\Entity\Craftsman;
use App\Helper\DateTimeFormatter;
use App\Service\Analysis\CraftsmanAnalysis;
use App\Service\Analysis\IssueAnalysis;
use Doctrine\ORM\QueryBuilder;

interface AnalysisServiceInterface
{
    /**
     * returns highest date first.
     *
     * @return IssueAnalysis[]
     */
    public function createIssueAnalysisByTime(string $rootAlias, QueryBuilder $queryBuilder, \DateTime $lastPeriodEnd, \DateInterval $stepSize, int $stepCount, string $dateFormat = DateTimeFormatter::ISO_DATE_FORMAT): array;

    public function createIssueAnalysis(string $rootAlias, QueryBuilder $queryBuilder): IssueAnalysis;

    /**
     * @param Craftsman[] $craftsmen
     *
     * @return CraftsmanAnalysis[]
     */
    public function createCraftsmanAnalysisByCraftsman(array $craftsmen): array;
}
