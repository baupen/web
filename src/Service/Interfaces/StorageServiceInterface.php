<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\ConstructionSite;
use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Entity\ProtocolEntry;
use App\Entity\ProtocolEntryFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface StorageServiceInterface
{
    public function setNewFolderName(ConstructionSite $constructionSite);

    public function uploadConstructionSiteImage(UploadedFile $file, ConstructionSite $constructionSite): ?ConstructionSiteImage;

    public function uploadMapFile(UploadedFile $file, Map $map): ?MapFile;

    public function uploadIssueImage(UploadedFile $file, Issue $issue): ?IssueImage;

    public function uploadProtocolEntryFile(UploadedFile $file, ProtocolEntry $protocolEntry): ?ProtocolEntryFile;
}
