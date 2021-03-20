<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

use App\Service\Craftsman\Statistics;

class CraftsmanStatistics extends Statistics
{
    /**
     * @var string
     */
    private $craftsman;

    /**
     * CraftsmanStatistics constructor.
     */
    public function __construct(string $craftsmanIri, Statistics $statistics)
    {
        parent::__construct();

        $this->craftsman = $craftsmanIri;
        parent::writeFrom($statistics);
    }

    public function getCraftsman(): string
    {
        return $this->craftsman;
    }

    public function setCraftsman(string $craftsman): void
    {
        $this->craftsman = $craftsman;
    }
}
