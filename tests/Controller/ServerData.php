<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/21/18
 * Time: 9:49 AM
 */

namespace App\Tests\Controller;


use App\Api\Entity\Building;
use App\Api\Entity\Craftsman;
use App\Api\Entity\Issue;
use App\Api\Entity\Map;

class ServerData
{
    /**
     * @var Building[]
     */
    private $buildings;

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
     * @param Building[] $buildings
     * @param Map[] $maps
     * @param Craftsman[] $craftsman
     * @param Issue[] $issues
     */
    public function __construct($buildings, $maps, $craftsman, $issues)
    {
        $this->buildings = $buildings;
        $this->maps = $maps;
        $this->craftsmen = $craftsman;
        $this->issues = $issues;
    }

    /**
     * @return Building[]
     */
    public function getBuildings(): array
    {
        return $this->buildings;
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