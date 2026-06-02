<?php

namespace App\Service\Interfaces;

use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Service\Report\Email\ConstructionSiteReport;
use App\Service\Report\Email\CraftsmanReport;
use App\Service\Report\Pdf\ReportElements;

interface ReportServiceInterface
{
    public function generatePdfReport(array $issues, Filter $filter, ReportElements $reportElements, ?string $author = null): string;

    public function createConstructionSiteReport(ConstructionSite $constructionSite, \DateTimeImmutable $comparisonTimestamp): ConstructionSiteReport;

    public function createCraftsmanReport(Craftsman $craftsman, ?\DateTimeImmutable $comparisonTimestamp): CraftsmanReport;
}
