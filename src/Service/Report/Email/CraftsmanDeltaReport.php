<?php

namespace App\Service\Report\Email;

use App\Entity\Craftsman;

class CraftsmanDeltaReport
{
    use IssueCountTrait;
    use IssueCountDeltaTrait;
    private Craftsman $craftsman;

    /**
     * CraftsmanDeltaReport constructor.
     */
    public function __construct(Craftsman $craftsman)
    {
        $this->craftsman = $craftsman;
    }

    public function getCraftsman(): Craftsman
    {
        return $this->craftsman;
    }
}
