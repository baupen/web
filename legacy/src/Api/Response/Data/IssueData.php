<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data;

use App\Api\Entity\Base\Issue;

class IssueData
{
    /**
     * @var Issue
     */
    private $issue;

    public function getIssue(): Issue
    {
        return $this->issue;
    }

    public function setIssue(Issue $issue): void
    {
        $this->issue = $issue;
    }
}
