<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Share\Filter;

use App\Api\Entity\Base\PublicIssue;
use DateTime;

class Issue extends PublicIssue
{
    /**
     * @var DateTime|null
     */
    private $reviewedAt;

    /**
     * @var string|null
     */
    private $reviewedByName;

    public function getReviewedAt(): ?DateTime
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?DateTime $reviewedAt): void
    {
        $this->reviewedAt = $reviewedAt;
    }

    public function getReviewedByName(): ?string
    {
        return $this->reviewedByName;
    }

    public function setReviewedByName(?string $reviewedByName): void
    {
        $this->reviewedByName = $reviewedByName;
    }
}
