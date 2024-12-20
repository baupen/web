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
use App\Entity\IssueEventFile;
use App\Entity\IssueImage;
use App\Entity\MapFile;

/**
 * exposes paths.
 *
 * "normal" folders are where the persistent data is stored.
 * this files are backed up regularly, and must contain all relevant data for the construction site
 *
 * "transient" folders are where the cached files are located.
 * this files may be removed at any moment, hence never assume the folders there exist or are filled.
 *
 * "transient/construction_sites" are files which never become invalid (like thumbnails of issue images)
 * "transient/renders" are files which become invalid if the data changes (like issue position render on map files)
 * "transient/reports" contains the generated reports; they should be removed automatically after the download finishes
 * "transient/authorization" contains any authorization data (likely managed outside this application)
 *
 * when uploading data, place them in the "normal" folders, then store information to the database.
 * afterwards, warm up the cache in the transient folders
 *
 * Interface PathServiceInterface
 */
interface PathServiceInterface
{
    public function getDatabaseBackupFolder(): string;

    public function getRootFolderOfConstructionSites(): string;

    public function getFolderForConstructionSiteImages(ConstructionSite $constructionSite): string;

    public function getFolderForMapFiles(ConstructionSite $constructionSite): string;

    public function getFolderForIssueImages(ConstructionSite $constructionSite): string;

    public function getFolderForIssueEventFiles(ConstructionSite $constructionSite): string;

    public function getTransientFolderForAuthorization(): string;

    public function getTransientFolderForConstructionSites(): string;

    public function getTransientFolderForReports(): string;

    public function getTransientFolderForRenders(): string;

    public function getTransientFolderForConstructionSiteImages(ConstructionSiteImage $constructionSiteImage): string;

    public function getTransientFolderForMapFile(MapFile $mapFile): string;

    public function getTransientFolderForIssueImage(IssueImage $issueImage): string;

    public function getTransientFolderForIssueEventFile(IssueEventFile $issueEventFile): string;

    public function getTransientFolderForMapFileRenders(MapFile $mapFile): string;

    public function getSampleConstructionSite(string $name): string;
}
