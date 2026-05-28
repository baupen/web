<?php

namespace App\Service\Interfaces;

use App\Entity\ConstructionSite;
use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueEvent;
use App\Entity\IssueEventFile;
use App\Entity\IssueImage;
use App\Entity\Map;
use App\Entity\MapFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface StorageServiceInterface
{
    public function setNewFolderName(ConstructionSite $constructionSite);

    public function uploadConstructionSiteImage(UploadedFile $file, ConstructionSite $constructionSite): ?ConstructionSiteImage;

    public function uploadMapFile(UploadedFile $file, Map $map): ?MapFile;

    public function uploadIssueImage(UploadedFile $file, Issue $issue): ?IssueImage;

    public function uploadIssueEventFile(UploadedFile $file, IssueEvent $issueEvent): ?IssueEventFile;
}
