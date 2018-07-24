<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data\Statistics;

use App\Api\Entity\Statistics\Overview;

class OverviewData
{
    /**
     * @var Overview
     */
    private $overview;

    /**
     * @return Overview
     */
    public function getOverview(): Overview
    {
        return $this->overview;
    }

    /**
     * @param Overview $overview
     */
    public function setOverview(Overview $overview): void
    {
        $this->overview = $overview;
    }
}
