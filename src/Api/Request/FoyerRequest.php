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

class FoyerRequest extends ConstructionSiteRequest
{
    /**
     * @var Issue[]|null
     */
    private $issues;

    /**
     * @var string[]|null
     */
    private $issueIds;

    /**
     * @return Issue[]|null
     */
    public function getIssues(): ?array
    {
        return $this->issues;
    }

    /**
     * @param Issue[]|null $issues
     */
    public function setIssues(?array $issues): void
    {
        $this->issues = $issues;
    }

    /**
     * @return null|string[]
     */
    public function getIssueIds(): ?array
    {
        return $this->issueIds;
    }

    /**
     * @param null|string[] $issueIds
     */
    public function setIssueIds(?array $issueIds): void
    {
        $this->issueIds = $issueIds;
    }
}
