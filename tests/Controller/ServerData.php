<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller;

use App\Api\External\Entity\ConstructionSite;
use App\Api\External\Entity\Craftsman;
use App\Api\External\Entity\Issue;
use App\Api\External\Entity\Map;

class ServerData
{
    /**
     * @var ConstructionSite[]
     */
    private $constructionSites;

    /**
     * @var Map[]
     */
    private $maps;

    /**
     * @var Craftsman[]
     */
    private $craftsmen;

    /**
     * @var Issue[]
     */
    private $issues;

    /**
     * ServerData constructor.
     *
     * @param ConstructionSite[] $constructionSites
     * @param Map[] $maps
     * @param Craftsman[] $craftsman
     * @param Issue[] $issues
     */
    public function __construct($constructionSites, $maps, $craftsman, $issues)
    {
        $this->constructionSites = $constructionSites;
        $this->maps = $maps;
        $this->craftsmen = $craftsman;
        $this->issues = $issues;
    }

    /**
     * @return ConstructionSite[]
     */
    public function getConstructionSites(): array
    {
        return $this->constructionSites;
    }

    /**
     * @return Map[]
     */
    public function getMaps(): array
    {
        return $this->maps;
    }

    /**
     * @return Craftsman[]
     */
    public function getCraftsmen(): array
    {
        return $this->craftsmen;
    }

    /**
     * @return Issue[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }
}
