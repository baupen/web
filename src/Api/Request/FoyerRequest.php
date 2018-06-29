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

use App\Api\Entity\Foyer\Issue;
use App\Api\Request\Base\AbstractRequest;

class FoyerRequest extends AbstractRequest
{
    /**
     * @var Issue[]
     */
    private $issues;

    /**
     * @var string[]
     */
    private $issueIds;

    /**
     * @return Issue[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /**
     * @param Issue[] $issues
     */
    public function setIssues(array $issues): void
    {
        $this->issues = $issues;
    }

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
