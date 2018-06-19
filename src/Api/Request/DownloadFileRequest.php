<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 10:37 AM
 */

namespace App\Api\Request;


use App\Api\Entity\Building;
use App\Api\Entity\Issue;
use App\Api\Entity\Map;
use App\Api\Request\Base\AuthenticatedRequest;

class DownloadFileRequest extends AuthenticatedRequest
{
    /**
     * @var Building|null
     */
    private $building;

    /**
     * @var Map|null
     */
    private $map;

    /**
     * @var Issue|null
     */
    private $issue;

    /**
     * @return Building|null
     */
    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    /**
     * @param Building|null $building
     */
    public function setBuilding(?Building $building): void
    {
        $this->building = $building;
    }

    /**
     * @return Map|null
     */
    public function getMap(): ?Map
    {
        return $this->map;
    }

    /**
     * @param Map|null $map
     */
    public function setMap(?Map $map): void
    {
        $this->map = $map;
    }

    /**
     * @return Issue|null
     */
    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    /**
     * @param Issue|null $issue
     */
    public function setIssue(?Issue $issue): void
    {
        $this->issue = $issue;
    }
}