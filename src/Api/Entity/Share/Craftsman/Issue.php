<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Share\Craftsman;

use App\Api\Entity\Base\PublicIssue;
use DateTime;

class Issue extends PublicIssue
{
    /**
     * @var DateTime|null
     */
    private $responseLimit;

    /**
     * @return DateTime|null
     */
    public function getResponseLimit(): ?DateTime
    {
        return $this->responseLimit;
    }

    /**
     * @param DateTime|null $responseLimit
     */
    public function setResponseLimit(?DateTime $responseLimit): void
    {
        $this->responseLimit = $responseLimit;
    }
}
