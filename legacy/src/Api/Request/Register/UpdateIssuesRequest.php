<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request\Register;

use App\Api\Entity\Register\UpdateIssue;
use App\Api\Request\ConstructionSiteRequest;

class UpdateIssuesRequest extends ConstructionSiteRequest
{
    /**
     * @var UpdateIssue[]
     */
    private $updateIssues;

    /**
     * @return UpdateIssue[]
     */
    public function getUpdateIssues(): array
    {
        return $this->updateIssues;
    }

    /**
     * @param UpdateIssue[] $updateIssues
     */
    public function setUpdateIssues(array $updateIssues): void
    {
        $this->updateIssues = $updateIssues;
    }
}
