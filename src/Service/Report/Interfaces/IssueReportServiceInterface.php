<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Interfaces;

use App\Service\Report\Document\Interfaces\DocumentLayoutInterface;

interface IssueReportServiceInterface
{
    /**
     * @param DocumentLayoutInterface $document
     * @param string $constructionSiteName
     * @param string|null $constructionSiteImage
     * @param string $constructionSiteAddressLines
     * @param string $reportElements
     * @param array $filterEntries
     */
    public function addIntroduction(DocumentLayoutInterface $document, string $constructionSiteName, ?string $constructionSiteImage, string $constructionSiteAddressLines, string $reportElements, array $filterEntries);

    /**
     * @param DocumentLayoutInterface $document
     * @param string $tableDescription
     * @param string[] $identifierHeader
     * @param string[] $identifierContent
     * @param string[] $issuesHeader
     * @param string[] $issuesContent
     */
    public function addAggregatedIssueTable(DocumentLayoutInterface $document, string $tableDescription, array $identifierHeader, array $identifierContent, array $issuesHeader, array $issuesContent);

    /**
     * @param DocumentLayoutInterface $report
     * @param string $mapName
     * @param string $mapContext
     * @param string|null $mapImage
     * @param string[] $issuesTableHeader
     * @param string[][] $issuesTableContent
     * @param string[] $images
     */
    public function addMap(DocumentLayoutInterface $report, string $mapName, string $mapContext, ?string $mapImage, array $issuesTableHeader, array $issuesTableContent, array $images);
}
