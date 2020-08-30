<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request\Register;

use App\Api\Request\IssueIdsRequest;

class SetStatusRequest extends IssueIdsRequest
{
    /**
     * @var bool
     */
    private $respondedStatusSet;

    /**
     * @var bool
     */
    private $reviewedStatusSet;

    public function isRespondedStatusSet(): bool
    {
        return $this->respondedStatusSet;
    }

    public function setRespondedStatusSet(bool $respondedStatusSet): void
    {
        $this->respondedStatusSet = $respondedStatusSet;
    }

    public function isReviewedStatusSet(): bool
    {
        return $this->reviewedStatusSet;
    }

    public function setReviewedStatusSet(bool $reviewedStatusSet): void
    {
        $this->reviewedStatusSet = $reviewedStatusSet;
    }
}
