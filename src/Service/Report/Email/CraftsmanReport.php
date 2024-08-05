<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Email;

use App\Entity\Craftsman;

class CraftsmanReport extends CraftsmanDeltaReport
{
    private ?\DateTime $comparisonTimestamp;

    public function __construct(Craftsman $craftsman, ?\DateTime $comparisonTimestamp)
    {
        parent::__construct($craftsman);

        $this->comparisonTimestamp = $comparisonTimestamp;
    }

    public function getComparisonTimestamp(): ?\DateTime
    {
        return $this->comparisonTimestamp;
    }
}
