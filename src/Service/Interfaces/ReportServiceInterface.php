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

use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Service\Report\Email\ConstructionSiteReport;
use App\Service\Report\Email\CraftsmanReport;
use App\Service\Report\Pdf\ReportElements;
use DateTime;

interface ReportServiceInterface
{
    public function generatePdfReport(array $issues, Filter $filter, ReportElements $reportElements, ?string $author = null): string;

    public function createConstructionSiteReport(ConstructionSite $constructionSite, DateTime $comparisonTimestamp): ConstructionSiteReport;

    public function createCraftsmanReport(Craftsman $craftsman, DateTime $comparisonTimestamp): CraftsmanReport;
}
