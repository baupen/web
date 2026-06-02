<?php

namespace App\Service\Report\Email;

use App\Entity\Craftsman;

class CraftsmanReport extends CraftsmanDeltaReport
{
    private ?\DateTimeImmutable $comparisonTimestamp;

    public function __construct(Craftsman $craftsman, ?\DateTimeImmutable $comparisonTimestamp)
    {
        parent::__construct($craftsman);

        $this->comparisonTimestamp = $comparisonTimestamp;
    }

    public function getComparisonTimestamp(): ?\DateTimeImmutable
    {
        return $this->comparisonTimestamp;
    }
}
