<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Foyer;

use DateTime;

class UpdateIssue extends \App\Api\Entity\Base\Issue
{
    /**
     * @var bool
     */
    private $isMarked;

    /**
     * @var string|null
     */
    private $craftsmanId;

    /**
     * @var DateTime|null
     */
    private $responseLimit;

    public function getIsMarked(): bool
    {
        return $this->isMarked;
    }

    public function setIsMarked(bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    public function getCraftsmanId(): ?string
    {
        return $this->craftsmanId;
    }

    public function setCraftsmanId(?string $craftsmanId): void
    {
        $this->craftsmanId = $craftsmanId;
    }

    public function getResponseLimit(): ?DateTime
    {
        return $this->responseLimit;
    }

    public function setResponseLimit(?DateTime $responseLimit): void
    {
        $this->responseLimit = $responseLimit;
    }
}
