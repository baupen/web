<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\Map;
use App\Service\Interfaces\PathServiceInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class PathService implements PathServiceInterface
{
    /**
     * @var string
     */
    private $folderRoot;

    /**
     * @var string
     */
    private $transientFolderRoot;

    /**
     * @var string
     */
    private $constructionSiteFolderRoot;

    public function __construct(KernelInterface $kernel)
    {
        $baseDir = $kernel->getLogDir() . \DIRECTORY_SEPARATOR . '..';

        // add test to path to be able to unit test without messing up local dev state
        $environment = $kernel->getEnvironment();
        if ($environment === 'test') {
            $baseDir .= \DIRECTORY_SEPARATOR . $environment;
        }

        $this->folderRoot = $baseDir . \DIRECTORY_SEPARATOR . 'persistent';
        $this->transientFolderRoot = $baseDir . \DIRECTORY_SEPARATOR . 'transient';

        $this->constructionSiteFolderRoot = $this->folderRoot . \DIRECTORY_SEPARATOR . 'construction_sites';
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    private function getFolderForConstructionSite(ConstructionSite $constructionSite)
    {
        return $this->getConstructionSiteFolderRoot() . \DIRECTORY_SEPARATOR . $constructionSite->getFolderName();
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForConstructionSiteImage(ConstructionSite $constructionSite)
    {
        return $this->getFolderForConstructionSite($constructionSite) . \DIRECTORY_SEPARATOR . 'images';
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForMapFile(ConstructionSite $constructionSite)
    {
        return $this->getFolderForConstructionSite($constructionSite) . \DIRECTORY_SEPARATOR . 'maps';
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForIssueImage(ConstructionSite $constructionSite)
    {
        return $this->getFolderForConstructionSite($constructionSite) . \DIRECTORY_SEPARATOR . 'issues';
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    private function getTransientFolderForConstructionSite(ConstructionSite $constructionSite)
    {
        return $this->getTransientFolderRoot() . \DIRECTORY_SEPARATOR . 'construction_sites' . \DIRECTORY_SEPARATOR . $constructionSite->getFolderName();
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getTransientFolderForConstructionSiteImage(ConstructionSite $constructionSite)
    {
        return $this->getTransientFolderForConstructionSite($constructionSite) . \DIRECTORY_SEPARATOR . 'images';
    }

    /**
     * @param Map $map
     *
     * @return string
     */
    public function getTransientFolderForMapFile(Map $map)
    {
        return $this->getTransientFolderForConstructionSite($map->getConstructionSite()) . \DIRECTORY_SEPARATOR . 'maps' . \DIRECTORY_SEPARATOR . $map->getId();
    }

    /**
     * @param Issue $issue
     *
     * @return string
     */
    public function getTransientFolderForIssueImage(Issue $issue)
    {
        return $this->getTransientFolderForConstructionSite($issue->getMap()->getConstructionSite()) . \DIRECTORY_SEPARATOR . 'issues' . \DIRECTORY_SEPARATOR . $issue->getId();
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getTransientFolderForReports(ConstructionSite $constructionSite)
    {
        return $this->getTransientFolderForConstructionSite($constructionSite) . \DIRECTORY_SEPARATOR . 'reports';
    }

    /**
     * @return string
     */
    public function getFolderRoot()
    {
        return $this->folderRoot;
    }

    /**
     * @return string
     */
    public function getConstructionSiteFolderRoot()
    {
        return $this->constructionSiteFolderRoot;
    }

    /**
     * @return string
     */
    public function getTransientFolderRoot()
    {
        return $this->transientFolderRoot;
    }
}
