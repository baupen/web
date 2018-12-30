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

class Color
{
    /**
     * @var int[]
     */
    private $textColor = [37, 40, 32];

    /**
     * @var int[]
     */
    private $secondaryTextColor = [68, 73, 58];

    /**
     * the color of lines & others.
     *
     * @var int[]
     */
    private $drawColor = [200, 200, 200];

    /**
     * @var int[]
     */
    private $background = [230, 230, 230];

    /**
     * @var int[]
     */
    private $secondaryBackground = [240, 240, 240];

    /**
     * @return int[]
     */
    public function getTextColor(): array
    {
        return $this->textColor;
    }

    /**
     * @return int[]
     */
    public function getSecondaryTextColor(): array
    {
        return $this->secondaryTextColor;
    }

    /**
     * @return int[]
     */
    public function getDrawColor(): array
    {
        return $this->drawColor;
    }

    /**
     * @return int[]
     */
    public function getBackground(): array
    {
        return $this->background;
    }

    /**
     * @return int[]
     */
    public function getSecondaryBackground(): array
    {
        return $this->secondaryBackground;
    }
}
