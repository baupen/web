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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PathService implements PathServiceInterface
{
    /**
     * @var string
     */
    private $publicDir;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->publicDir = $parameterBag->get('PUBLIC_DIR');
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForConstructionSite(ConstructionSite $constructionSite)
    {
        return $this->getFolderRoot() . \DIRECTORY_SEPARATOR . 'construction_sites' . \DIRECTORY_SEPARATOR . $constructionSite->getFolderName();
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForMap(ConstructionSite $constructionSite)
    {
        return $this->getFolderForConstructionSite($constructionSite) . \DIRECTORY_SEPARATOR . 'maps';
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForIssue(ConstructionSite $constructionSite)
    {
        return $this->getFolderForConstructionSite($constructionSite) . \DIRECTORY_SEPARATOR . 'issues';
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getTransientFolderForConstructionSite(ConstructionSite $constructionSite)
    {
        return $this->getTransientFolderRoot() . \DIRECTORY_SEPARATOR . 'construction_sites' . \DIRECTORY_SEPARATOR . $constructionSite->getFolderName();
    }

    /**
     * @param Map $map
     *
     * @return string
     */
    public function getTransientFolderForMap(Map $map)
    {
        return $this->getTransientFolderForConstructionSite($map->getConstructionSite()) . \DIRECTORY_SEPARATOR . 'maps' . \DIRECTORY_SEPARATOR . $map->getFilename();
    }

    /**
     * @param Issue $issue
     *
     * @return string
     */
    public function getTransientFolderForIssue(Issue $issue)
    {
        return $this->getTransientFolderForConstructionSite($issue->getMap()->getConstructionSite()) . \DIRECTORY_SEPARATOR . 'issues' . $issue->getImageFilename();
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
        return $this->publicDir . \DIRECTORY_SEPARATOR . 'persistent';
    }

    /**
     * @return string
     */
    public function getTransientFolderRoot()
    {
        return $this->publicDir . \DIRECTORY_SEPARATOR . 'transient';
    }
}
