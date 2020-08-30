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
    private $constructionSite;

    /**
     * @var ObjectMeta|null
     */
    private $map;

    /**
     * @var ObjectMeta|null
     */
    private $issue;

    public function getConstructionSite(): ?ObjectMeta
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(?ObjectMeta $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function getMap(): ?ObjectMeta
    {
        return $this->map;
    }

    public function setMap(?ObjectMeta $map): void
    {
        $this->map = $map;
    }

    public function getIssue(): ?ObjectMeta
    {
        return $this->issue;
    }

    public function setIssue(?ObjectMeta $issue): void
    {
        $this->issue = $issue;
    }
}
