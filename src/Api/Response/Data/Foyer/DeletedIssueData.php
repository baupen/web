<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data\Foyer;

use App\Api\Entity\Base\BaseEntity;

class DeletedIssueData
{
    /**
     * @var BaseEntity[]
     */
    private $deletedIssues;

    /**
     * @return BaseEntity[]
     */
    public function getDeletedIssues(): array
    {
        return $this->deletedIssues;
    }

    /**
     * @param BaseEntity[] $deletedIssues
     */
    public function setDeletedIssues(array $deletedIssues): void
    {
        $this->deletedIssues = $deletedIssues;
    }
}
