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
use const DIRECTORY_SEPARATOR;
use Symfony\Component\HttpKernel\KernelInterface;

class PathService implements PathServiceInterface
{
    /**
     * @var string
     */
    private $scriptsRoot;

    /**
     * @var string
     */
    private $assetsRoot;

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

    /**
     * PathService constructor.
     */
    public function __construct(KernelInterface $kernel)
    {
        $varDir = realpath($kernel->getLogDir() . DIRECTORY_SEPARATOR . '..');
        $this->assetsRoot = realpath($varDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets');
        $this->scriptsRoot = realpath($varDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'supporting');

        // add test to path to be able to unit test without messing up local dev state
        $environment = $kernel->getEnvironment();
        if ($environment === 'test') {
            $varDir .= DIRECTORY_SEPARATOR . $environment;
        }

        $this->folderRoot = $varDir . DIRECTORY_SEPARATOR . 'persistent';
        $this->transientFolderRoot = $varDir . DIRECTORY_SEPARATOR . 'transient';

        $this->constructionSiteFolderRoot = $this->folderRoot . DIRECTORY_SEPARATOR . 'construction_sites';
    }

    /**
     * @return string
     */
    public function getFolderForConstructionSiteImage(ConstructionSite $constructionSite)
    {
        return $this->getFolderForConstructionSite($constructionSite) . DIRECTORY_SEPARATOR . 'images';
    }

    /**
     * @return string
     */
    public function getFolderForMapFile(ConstructionSite $constructionSite)
    {
        return $this->getFolderForConstructionSite($constructionSite) . DIRECTORY_SEPARATOR . 'maps';
    }

    /**
     * @return string
     */
    public function getFolderForIssueImage(ConstructionSite $constructionSite)
    {
        return $this->getFolderForConstructionSite($constructionSite) . DIRECTORY_SEPARATOR . 'issues';
    }

    /**
     * @return string
     */
    public function getTransientFolderForConstructionSiteImage(ConstructionSite $constructionSite)
    {
        return $this->getTransientFolderForConstructionSite($constructionSite) . DIRECTORY_SEPARATOR . 'images';
    }

    /**
     * @return string
     */
    public function getTransientFolderForMapFile(Map $map)
    {
        return $this->getTransientFolderForConstructionSite($map->getConstructionSite()) . DIRECTORY_SEPARATOR . 'maps' . DIRECTORY_SEPARATOR . $map->getId();
    }

    /**
     * @return string
     */
    public function getTransientFolderForIssueImage(Issue $issue)
    {
        return $this->getTransientFolderForConstructionSite($issue->getMap()->getConstructionSite()) . DIRECTORY_SEPARATOR . 'issues' . DIRECTORY_SEPARATOR . $issue->getId();
    }

    /**
     * @return string
     */
    public function getTransientFolderForReports(ConstructionSite $constructionSite)
    {
        return $this->getTransientFolderForConstructionSite($constructionSite) . DIRECTORY_SEPARATOR . 'reports';
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

    /**
     * @return string
     */
    public function getScriptsRoot()
    {
        return $this->scriptsRoot;
    }

    /**
     * @return string
     */
    public function getAssetsRoot()
    {
        return $this->assetsRoot;
    }

    /**
     * @return string
     */
    private function getFolderForConstructionSite(ConstructionSite $constructionSite)
    {
        return $this->getConstructionSiteFolderRoot() . DIRECTORY_SEPARATOR . $constructionSite->getFolderName();
    }

    /**
     * @return string
     */
    private function getTransientFolderForConstructionSite(ConstructionSite $constructionSite)
    {
        return $this->getTransientFolderRoot() . DIRECTORY_SEPARATOR . 'construction_sites' . DIRECTORY_SEPARATOR . $constructionSite->getFolderName();
    }
}
