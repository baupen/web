<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Configuration;

class Typography
{
    /**
     * font must be available in assets/fonts/tcpdf in the format used by tcpdf.
     *
     * @var string
     */
    private $fontFamily = 'opensans';

    /**
     * @var float
     */
    private $baseFontSize = 8;

    /**
     * @var float
     */
    private $lineWidth = 0.2;

    /**
     * @return string
     */
    public function getFontFamily(): string
    {
        return $this->fontFamily;
    }

    /**
     * @return float
     */
    public function getBaseFontSize(): float
    {
        return $this->baseFontSize;
    }

    /**
     * @return float
     */
    public function getLineWidth(): float
    {
        return $this->lineWidth;
    }
}
