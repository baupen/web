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
