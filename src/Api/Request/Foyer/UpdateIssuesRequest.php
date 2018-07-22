<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request\Foyer;

use App\Api\Entity\Foyer\Issue;
use App\Api\Entity\Foyer\UpdateIssue;
use App\Api\Request\ConstructionSiteRequest;

class UpdateIssuesRequest extends ConstructionSiteRequest
{
    /**
     * @var UpdateIssue[]
     */
    private $updateIssues;

    /**
     * @return Issue[]
     */
    public function getUpdateIssues(): array
    {
        return $this->updateIssues;
    }

    /**
     * @param Issue[] $updateIssues
     */
    public function setUpdateIssues(array $updateIssues): void
    {
        $this->updateIssues = $updateIssues;
    }
}
