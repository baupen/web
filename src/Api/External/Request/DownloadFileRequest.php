<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Request;

use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\Base\AuthenticatedRequest;

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
