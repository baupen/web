<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Design;

use App\Service\Report\Pdf\Configuration\Color;
use App\Service\Report\Pdf\Design\Interfaces\ColorServiceInterface;

class ColorService implements ColorServiceInterface
{
    /**
     * @var Color
     */
    private $color;

    /**
     * ColorService constructor.
     */
    public function __construct()
    {
        $this->color = new Color();
    }

    /**
     * @return int[]
     */
    public function getTextColor()
    {
        return $this->color->getTextColor();
    }

    /**
     * @return int[]
     */
    public function getImageOverlayColor()
    {
        return $this->color->getBackground();
    }

    /**
     * @return int[]
     */
    public function getTableAlternatingBackgroundColor()
    {
        return $this->color->getSecondaryBackground();
    }
}
