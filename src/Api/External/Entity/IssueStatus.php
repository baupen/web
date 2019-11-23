<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Entity;

class IssueStatus
{
    /**
     * @var IssueStatusEvent|null
     */
    private $registration;

    /**
     * @var IssueStatusEvent|null
     */
    private $response;

    /**
     * @var IssueStatusEvent|null
     */
    private $review;

    public function getRegistration(): ?IssueStatusEvent
    {
        return $this->registration;
    }

    public function setRegistration(?IssueStatusEvent $registration): void
    {
        $this->registration = $registration;
    }

    public function getResponse(): ?IssueStatusEvent
    {
        return $this->response;
    }

    public function setResponse(?IssueStatusEvent $response): void
    {
        $this->response = $response;
    }

    public function getReview(): ?IssueStatusEvent
    {
        return $this->review;
    }

    public function setReview(?IssueStatusEvent $review): void
    {
        $this->review = $review;
    }
}
