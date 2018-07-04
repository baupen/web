<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request;

class IssuesRequest extends ConstructionSiteRequest
{
    /**
     * @var string[]
     */
    private $issueIds;

    /**
     * @return string[]
     */
    public function getIssueIds(): array
    {
        return $this->issueIds;
    }

    /**
     * @param string[] $issueIds
     */
    public function setIssueIds(array $issueIds): void
    {
        $this->issueIds = $issueIds;
    }
}
