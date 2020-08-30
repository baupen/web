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

use App\Api\Entity\Foyer\NumberIssue;

class NumberIssueData
{
    /**
     * @var NumberIssue[]
     */
    private $numberIssues;

    /**
     * @return NumberIssue[]
     */
    public function getNumberIssues(): array
    {
        return $this->numberIssues;
    }

    /**
     * @param NumberIssue[] $numberIssues
     */
    public function setNumberIssues(array $numberIssues): void
    {
        $this->numberIssues = $numberIssues;
    }
}
