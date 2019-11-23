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

use App\Api\External\Entity\Issue;
use App\Api\External\Request\Base\AuthenticatedRequest;
use Symfony\Component\Validator\Constraints as Assert;

class IssueModifyRequest extends AuthenticatedRequest
{
    /**
     * @var Issue
     *
     * @Assert\NotBlank()
     */
    private $issue;

    public function getIssue(): Issue
    {
        return $this->issue;
    }

    /**
     * @param Issue $issue
     */
    public function setIssue($issue): void
    {
        $this->issue = $issue;
    }
}
