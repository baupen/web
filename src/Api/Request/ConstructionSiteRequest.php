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

use App\Api\Request\Base\AbstractRequest;
use Symfony\Component\Validator\Constraints as Assert;

class ConstructionSiteRequest extends AbstractRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $constructionSiteId;

    public function getConstructionSiteId(): string
    {
        return $this->constructionSiteId;
    }

    public function setConstructionSiteId(string $constructionSiteId): void
    {
        $this->constructionSiteId = $constructionSiteId;
    }
}
