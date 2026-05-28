<?php

namespace App\Service\Interfaces;

use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Service\Report\Email\CraftsmanReport;

interface EmailServiceInterface
{
    public function sendRegisterConfirmLink(ConstructionManager $constructionManager): bool;

    public function sendRecoverConfirmLink(ConstructionManager $constructionManager): bool;

    public function sendAppInvitation(ConstructionManager $constructionManager): bool;

    public function sendCraftsmanIssueReminder(ConstructionManager $constructionManager, Craftsman $craftsman, CraftsmanReport $craftsmanReport, string $subject, string $body, bool $constructionManagerInBCC): bool;

    public function sendConstructionSitesReport(ConstructionManager $constructionManager, array $constructionSiteReports): bool;
}
