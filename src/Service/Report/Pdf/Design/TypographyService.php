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

use App\Service\Report\Pdf\Configuration\Typography;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;

class TypographyService implements TypographyServiceInterface
{
    /**
     * @var Typography
     */
    private $typography;

    /**
     * TypographyService constructor.
     */
    public function __construct()
    {
        $this->typography = new Typography();
    }

    /**
     * for footers/headers.
     *
     * @return float
     */
    private function getSmallFontSize()
    {
        return $this->getRegularFontSize() / $this->typography->getScalingFactor();
    }

    /**
     * for text.
     *
     * @return float
     */
    private function getRegularFontSize()
    {
        return $this->typography->getBaseFontSize();
    }

    /**
     * for text.
     *
     * @return float
     */
    private function getBigFontSize()
    {
        return $this->getRegularFontSize() * $this->typography->getScalingFactor();
    }

    /**
     * for footers/headers.
     *
     * @return float
     */
    public function getFooterFontSize()
    {
        return $this->getSmallFontSize();
    }

    /**
     * for footers/headers.
     *
     * @return float
     */
    public function getHeaderFontSize()
    {
        return $this->getRegularFontSize();
    }

    /**
     * for text.
     *
     * @return float
     */
    public function getTextFontSize()
    {
        return $this->getRegularFontSize();
    }

    /**
     * for titles.
     *
     * @return float
     */
    public function getTitleFontSize()
    {
        return $this->getBigFontSize();
    }
}
