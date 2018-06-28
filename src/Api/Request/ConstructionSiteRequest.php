<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request;

use App\Api\External\Request\Base\AuthenticatedRequest;
use Symfony\Component\Validator\Constraints as Assert;

class ConstructionSiteRequest extends AuthenticatedRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $constructionSiteId;

    /**
     * @return string
     */
    public function getConstructionSiteId(): string
    {
        return $this->constructionSiteId;
    }

    /**
     * @param string $constructionSiteId
     */
    public function setConstructionSiteId(string $constructionSiteId): void
    {
        $this->constructionSiteId = $constructionSiteId;
    }
}
