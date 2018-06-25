<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Request;

use App\Api\Entity\Issue;
use App\Api\Request\Base\AuthenticatedRequest;
use Symfony\Component\Validator\Constraints as Assert;

class IssueModifyRequest extends AuthenticatedRequest
{
    /**
     * @var Issue
     *
     * @Assert\NotBlank()
     */
    private $issue;

    /**
     * @return Issue
     */
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
