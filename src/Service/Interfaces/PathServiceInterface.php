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
use App\Entity\Issue;
use App\Entity\Map;

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
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForConstructionSiteImage(ConstructionSite $constructionSite);

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForMapFile(ConstructionSite $constructionSite);

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForIssueImage(ConstructionSite $constructionSite);

    /**
     * @return string
     */
    public function getTransientFolderRoot();

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getTransientFolderForConstructionSiteImage(ConstructionSite $constructionSite);

    /**
     * @param Map $map
     *
     * @return string
     */
    public function getTransientFolderForMapFile(Map $map);

    /**
     * @param Issue $issue
     *
     * @return string
     */
    public function getTransientFolderForIssueImage(Issue $issue);

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getTransientFolderForReports(ConstructionSite $constructionSite);

    /**
     * @return string
     */
    public function getScriptsRoot();

    /**
     * @return string
     */
    public function getAssetsRoot();
}
