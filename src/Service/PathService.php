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
            $varDir .= DIRECTORY_SEPARATOR.'testing';
        }

        $this->folderRoot = $varDir.DIRECTORY_SEPARATOR.'persistent';
        $this->transientFolderRoot = $varDir.DIRECTORY_SEPARATOR.'transient';

        $this->constructionSiteFolderRoot = $this->folderRoot.DIRECTORY_SEPARATOR.'construction_sites';
    }

    public function getRootFolderOfConstructionSites(): string
    {
        return $this->constructionSiteFolderRoot;
    }

    public function getFolderForConstructionSiteImages(ConstructionSite $constructionSite): string
    {
        return $this->getFolderForConstructionSite($constructionSite).DIRECTORY_SEPARATOR.'images';
    }

    public function getFolderForMapFiles(ConstructionSite $constructionSite): string
    {
        return $this->getFolderForConstructionSite($constructionSite).DIRECTORY_SEPARATOR.'map_files';
    }

    public function getFolderForIssueImages(ConstructionSite $constructionSite): string
    {
        return $this->getFolderForConstructionSite($constructionSite).DIRECTORY_SEPARATOR.'issues';
    }

    public function getTransientFolderForConstructionSiteImages(ConstructionSiteImage $constructionSiteImage): string
    {
        return $this->getTransientFolderForConstructionSite($constructionSiteImage->getConstructionSite()).DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$constructionSiteImage->getFilename();
    }

    public function getTransientFolderForMapFile(MapFile $mapFile): string
    {
        return $this->getTransientFolderForConstructionSite($mapFile->getConstructionSite()).DIRECTORY_SEPARATOR.'map_files'.DIRECTORY_SEPARATOR.$mapFile->getFilename();
    }

    public function getTransientFolderForIssueImage(IssueImage $issueImage): string
    {
        return $this->getTransientFolderForConstructionSite($issueImage->getIssue()->getMap()->getConstructionSite()).DIRECTORY_SEPARATOR.'issues'.DIRECTORY_SEPARATOR.$issueImage->getFilename();
    }

    public function getTransientFolderForReports(ConstructionSite $constructionSite): string
    {
        return $this->getTransientFolderForConstructionSite($constructionSite).DIRECTORY_SEPARATOR.'reports';
    }

    private function getFolderForConstructionSite(ConstructionSite $constructionSite): string
    {
        return $this->constructionSiteFolderRoot.DIRECTORY_SEPARATOR.$constructionSite->getFolderName();
    }

    private function getTransientFolderForConstructionSite(ConstructionSite $constructionSite): string
    {
        return $this->transientFolderRoot.DIRECTORY_SEPARATOR.'construction_sites'.DIRECTORY_SEPARATOR.$constructionSite->getFolderName();
    }

    public function getTransientFolderForAuthorization(): string
    {
        return $this->transientFolderRoot.DIRECTORY_SEPARATOR.'authorization';
    }

    public function getSampleConstructionSite(string $name): string
    {
        return $this->assetsRoot.DIRECTORY_SEPARATOR.'samples'.DIRECTORY_SEPARATOR.$name;
    }
}
