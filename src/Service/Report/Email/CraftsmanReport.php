<?php

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
