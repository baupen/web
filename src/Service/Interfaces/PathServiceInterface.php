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
use App\Entity\ConstructionSiteImage;
use App\Entity\IssueImage;
use App\Entity\MapFile;

interface PathServiceInterface
{
    /**
     * @return string
     */
    public function getFolderRoot();

    /**
     * @return string
     */
    public function getConstructionSiteFolderRoot();

    /**
     * @return string
     */
    public function getFolderForConstructionSiteImage(ConstructionSiteImage $constructionSiteImage);

    /**
     * @return string
     */
    public function getFolderForMapFile(MapFile $mapFile);

    /**
     * @return string
     */
    public function getFolderForIssueImage(IssueImage $issueImage);

    /**
     * @return string
     */
    public function getTransientFolderRoot();

    /**
     * @return string
     */
    public function getTransientFolderForConstructionSiteImage(ConstructionSiteImage $constructionSiteImage);

    /**
     * @return string
     */
    public function getTransientFolderForMapFile(MapFile $mapFile);

    /**
     * @return string
     */
    public function getTransientFolderForIssueImage(IssueImage $issueImage);

    /**
     * @return string
     */
    public function getTransientFolderForReports(ConstructionSite $constructionSite);

    /**
     * @return string
     */
    public function getAssetsRoot();
}
