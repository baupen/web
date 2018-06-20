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
use App\Api\Entity\ObjectMeta;
use App\Api\Request\Base\AuthenticatedRequest;

class DownloadFileRequest extends AuthenticatedRequest
{
    /**
     * @var ObjectMeta|null
     */
    private $building;

    /**
     * @var ObjectMeta|null
     */
    private $map;

    /**
     * @var ObjectMeta|null
     */
    private $issue;

    /**
     * @return ObjectMeta|null
     */
    public function getBuilding(): ?ObjectMeta
    {
        return $this->building;
    }

    /**
     * @param ObjectMeta|null $building
     */
    public function setBuilding(?ObjectMeta $building): void
    {
        $this->building = $building;
    }

    /**
     * @return ObjectMeta|null
     */
    public function getMap(): ?ObjectMeta
    {
        return $this->map;
    }

    /**
     * @param ObjectMeta|null $map
     */
    public function setMap(?ObjectMeta $map): void
    {
        $this->map = $map;
    }

    /**
     * @return ObjectMeta|null
     */
    public function getIssue(): ?ObjectMeta
    {
        return $this->issue;
    }

    /**
     * @param ObjectMeta|null $issue
     */
    public function setIssue(?ObjectMeta $issue): void
    {
        $this->issue = $issue;
    }
}