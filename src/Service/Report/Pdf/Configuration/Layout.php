<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Configuration;

class Layout
{
    /**
     * the total page size; currently A4.
     *
     * @var float[]
     */
    private $pageSize = [210, 297];

    /**
     * margins of the page; left, top, right, bottom.
     *
     * @var float[]
     */
    private $pageMargins = [10, 6, 10, 6];

    /**
     * @var float
     */
    private $baseSpacing = 8;

    /**
     * @var float
     */
    private $scalingFactor = 1.6;

    /**
     * @return float[]
     */
    public function getPageSize(): array
    {
        return $this->pageSize;
    }

    /**
     * @return float[]
     */
    public function getPageMargins(): array
    {
        return $this->pageMargins;
    }

    /**
     * @return float
     */
    public function getBaseSpacing(): float
    {
        return $this->baseSpacing;
    }

    /**
     * @return float
     */
    public function getScalingFactor(): float
    {
        return $this->scalingFactor;
    }
}
