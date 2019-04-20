<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Pdf\Design;

use App\Service\Report\IssueReport\Pdf\Configuration\Layout;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\LayoutServiceInterface;

class LayoutService implements LayoutServiceInterface
{
    /**
     * @var Layout
     */
    private $layout;

    /**
     * @var TypographyService
     */
    private $typographyService;

    /**
     * LayoutService constructor.
     *
     * @param TypographyService $typographyService
     */
    public function __construct(TypographyService $typographyService)
    {
        $this->layout = new Layout();
        $this->typographyService = $typographyService;
    }

    /**
     * the total width of the document.
     *
     * @return float
     */
    public function getPageSizeY(): float
    {
        return $this->layout->getPageSize()[1];
    }

    /**
     * @return float
     */
    public function getHeaderYStart(): float
    {
        return $this->layout->getPageMargins()[1];
    }

    /**
     * @return float
     */
    public function getHeaderHeight(): float
    {
        return $this->typographyService->getHeaderFontSize();
    }

    /**
     * @return float
     */
    public function getContentXStart(): float
    {
        return $this->layout->getPageMargins()[0];
    }

    /**
     * the width of the document till the right margin.
     *
     * @return float
     */
    public function getContentXEnd(): float
    {
        return $this->getPageSizeX() - $this->layout->getPageMargins()[2];
    }

    /**
     * @return float
     */
    public function getContentXSize(): float
    {
        return $this->getContentXEnd() - $this->getContentXStart();
    }

    /**
     * @return float
     */
    public function getContentYStart(): float
    {
        return $this->getHeaderYStart() + $this->getHeaderHeight() + $this->layout->getBaseSpacing();
    }

    /**
     * the width of the document till the bottom margin.
     *
     * @return float
     */
    public function getContentYEnd(): float
    {
        return $this->getPageSizeY() - $this->layout->getPageMargins()[3] - $this->typographyService->getFooterFontSize() - $this->layout->getBaseSpacing();
    }

    /**
     * the width of the content of the document.
     *
     * @return float
     */
    public function getContentYSize(): float
    {
        return $this->getContentYEnd() - $this->getContentYStart();
    }

    /**
     * @return float
     */
    public function getFooterYStart(): float
    {
        return $this->getContentYEnd() + $this->layout->getBaseSpacing();
    }

    /**
     * @return float
     */
    public function getMarginBottom(): float
    {
        return $this->getPageSizeY() - $this->getFooterYStart() + $this->layout->getBaseSpacing();
    }

    /**
     * @return float
     */
    public function getMarginRight(): float
    {
        return $this->getPageSizeX() - $this->getContentXEnd();
    }

    /**
     * @return float
     */
    public function getColumnGutter(): float
    {
        return $this->layout->getBaseSpacing() / $this->layout->getScalingFactor();
    }

    /**
     * @return float
     */
    public function getRegionSpacer(): float
    {
        return $this->layout->getBaseSpacing() * ($this->layout->getScalingFactor() ** 2);
    }

    /**
     * @return float
     */
    public function getTableColumnGutter(): float
    {
        return $this->layout->getBaseSpacing() / ($this->layout->getScalingFactor() ** 2);
    }

    /**
     * the total width of the document.
     *
     * @return float
     */
    private function getPageSizeX()
    {
        return $this->layout->getPageSize()[0];
    }
}
