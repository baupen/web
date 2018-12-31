<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Services\Sync\Mock;

use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\Map;
use App\Service\Interfaces\PathServiceInterface;

class PathServiceMock implements PathServiceInterface
{
    /**
     * @var string
     */
    private $folder;

    public function __construct($folderToReturn)
    {
        $this->folder = $folderToReturn;
    }

    /**
     * @return string
     */
    public function getFolderRoot()
    {
        return $this->folder;
    }

    /**
     * @return string
     */
    public function getConstructionSiteFolderRoot()
    {
        return $this->folder;
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForConstructionSiteImage(ConstructionSite $constructionSite)
    {
        return $this->folder;
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForMapFile(ConstructionSite $constructionSite)
    {
        return $this->folder;
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForIssueImage(ConstructionSite $constructionSite)
    {
        return $this->folder;
    }

    /**
     * @return string
     */
    public function getTransientFolderRoot()
    {
        return $this->folder;
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getTransientFolderForConstructionSiteImage(ConstructionSite $constructionSite)
    {
        return $this->folder;
    }

    /**
     * @param Map $map
     *
     * @return string
     */
    public function getTransientFolderForMapFile(Map $map)
    {
        return $this->folder;
    }

    /**
     * @param Issue $issue
     *
     * @return string
     */
    public function getTransientFolderForIssueImage(Issue $issue)
    {
        return $this->folder;
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getTransientFolderForReports(ConstructionSite $constructionSite)
    {
        return $this->folder;
    }

    /**
     * @return string
     */
    public function getScriptsRoot()
    {
        return $this->folder;
    }

    /**
     * @return string
     */
    public function getAssetsRoot()
    {
        return $this->folder;
    }
}
