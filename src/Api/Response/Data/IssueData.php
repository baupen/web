<?php
/**
 * Created by PhpStorm.
 * Issue: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Response\Data;

use App\Api\Entity\Issue;

class IssueData
{
    /**
     * LoginData constructor.
     * @param Issue $issue
     */
    public function __construct(Issue $issue)
    {
        $this->issue = $issue;
    }

    /**
     * @var Issue $issue
     */
    private $issue;

    /**
     * @return Issue
     */
    public function getIssue(): Issue
    {
        return $this->issue;
    }
}
