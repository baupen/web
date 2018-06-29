<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Response\Data;

use App\Api\External\Entity\Issue;

class IssueData
{
    /**
     * LoginData constructor.
     *
     * @param Issue $issue
     */
    public function __construct(Issue $issue)
    {
        $this->issue = $issue;
    }

    /**
     * @var Issue
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
