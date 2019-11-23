<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Edit;

class MapFile extends \App\Api\Entity\Base\MapFile
{
    /**
     * @var int
     */
    private $issueCount;

    public function getIssueCount(): int
    {
        return $this->issueCount;
    }

    public function setIssueCount(int $issueCount): void
    {
        $this->issueCount = $issueCount;
    }
}
