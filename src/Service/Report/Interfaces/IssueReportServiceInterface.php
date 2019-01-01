<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Interfaces;

use App\Service\Report\Document\DocumentInterface;

interface IssueReportServiceInterface
{
    /**
     * @param DocumentInterface $document
     * @param string $constructionSiteName
     * @param string|null $constructionSiteImage
     * @param string $constructionSiteAddressLines
     * @param string $reportElements
     * @param array $filterEntries
     */
    public function addIntroduction(DocumentInterface $document, string $constructionSiteName, ?string $constructionSiteImage, string $constructionSiteAddressLines, string $reportElements, array $filterEntries);
}
