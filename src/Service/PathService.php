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
use App\Entity\ConstructionSiteImage;
use App\Entity\IssueImage;
use App\Entity\MapFile;
use App\Service\Interfaces\PathServiceInterface;
use const DIRECTORY_SEPARATOR;
use Symfony\Component\HttpKernel\KernelInterface;

class PathService implements PathServiceInterface
{
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
        $this->assetsRoot = realpath($kernel->getProjectDir().DIRECTORY_SEPARATOR.'assets');

        // add test to path to be able to unit test without messing up local dev state
        $environment = $kernel->getEnvironment();
        $varDir = $kernel->getProjectDir().DIRECTORY_SEPARATOR.'var';
        if ('test' === $environment) {
            $varDir .= DIRECTORY_SEPARATOR.$environment;
        }

        $this->folderRoot = $varDir.DIRECTORY_SEPARATOR.'persistent';
        $this->transientFolderRoot = $varDir.DIRECTORY_SEPARATOR.'transient';

        $this->constructionSiteFolderRoot = $this->folderRoot.DIRECTORY_SEPARATOR.'construction_sites';
    }

    /**
     * @return string
     */
    public function getFolderForConstructionSiteImage(ConstructionSiteImage $constructionSiteImage)
    {
        return $this->getFolderForConstructionSite($constructionSiteImage->getConstructionSite()).DIRECTORY_SEPARATOR.'images';
    }

    /**
     * @return string
     */
    public function getFolderForMapFile(MapFile $mapFile)
    {
        return $this->getFolderForConstructionSite($mapFile->getConstructionSite()).DIRECTORY_SEPARATOR.'maps';
    }

    /**
     * @return string
     */
    public function getFolderForIssueImage(IssueImage $issueImage)
    {
        return $this->getFolderForConstructionSite($issueImage->getIssue()->getMap()->getConstructionSite()).DIRECTORY_SEPARATOR.'issues';
    }

    /**
     * @return string
     */
    public function getTransientFolderForConstructionSiteImage(ConstructionSiteImage $constructionSiteImage)
    {
        return $this->getTransientFolderForConstructionSite($constructionSiteImage->getConstructionSite()).DIRECTORY_SEPARATOR.'images';
    }

    /**
     * @return string
     */
    public function getTransientFolderForMapFile(MapFile $mapFile)
    {
        return $this->getTransientFolderForConstructionSite($mapFile->getConstructionSite()).DIRECTORY_SEPARATOR.'maps'.DIRECTORY_SEPARATOR.$mapFile->getId();
    }

    /**
     * @return string
     */
    public function getTransientFolderForIssueImage(IssueImage $issueImage)
    {
        return $this->getTransientFolderForConstructionSite($issueImage->getIssue()->getMap()->getConstructionSite()).DIRECTORY_SEPARATOR.'issues'.DIRECTORY_SEPARATOR.$issueImage->getId();
    }

    /**
     * @return string
     */
    public function getTransientFolderForReports(ConstructionSite $constructionSite)
    {
        return $this->getTransientFolderForConstructionSite($constructionSite).DIRECTORY_SEPARATOR.'reports';
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
    public function getAssetsRoot()
    {
        return $this->assetsRoot;
    }

    /**
     * @return string
     */
    private function getFolderForConstructionSite(ConstructionSite $constructionSite)
    {
        return $this->getConstructionSiteFolderRoot().DIRECTORY_SEPARATOR.$constructionSite->getFolderName();
    }

    /**
     * @return string
     */
    private function getTransientFolderForConstructionSite(ConstructionSite $constructionSite)
    {
        return $this->getTransientFolderRoot().DIRECTORY_SEPARATOR.'construction_sites'.DIRECTORY_SEPARATOR.$constructionSite->getFolderName();
    }
}
