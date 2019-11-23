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
     */
    public function __construct(TypographyService $typographyService)
    {
        $this->layout = new Layout();
        $this->typographyService = $typographyService;
    }

    /**
     * the total width of the document.
     */
    public function getPageSizeY(): float
    {
        return $this->layout->getPageSize()[1];
    }

    public function getHeaderYStart(): float
    {
        return $this->layout->getPageMargins()[1];
    }

    public function getHeaderHeight(): float
    {
        return $this->typographyService->getHeaderFontSize();
    }

    public function getContentXStart(): float
    {
        return $this->layout->getPageMargins()[0];
    }

    /**
     * the width of the document till the right margin.
     */
    public function getContentXEnd(): float
    {
        return $this->getPageSizeX() - $this->layout->getPageMargins()[2];
    }

    public function getContentXSize(): float
    {
        return $this->getContentXEnd() - $this->getContentXStart();
    }

    public function getContentYStart(): float
    {
        return $this->getHeaderYStart() + $this->getHeaderHeight() + $this->layout->getBaseSpacing();
    }

    /**
     * the width of the document till the bottom margin.
     */
    public function getContentYEnd(): float
    {
        return $this->getPageSizeY() - $this->layout->getPageMargins()[3] - $this->typographyService->getFooterFontSize() - $this->layout->getBaseSpacing();
    }

    /**
     * the width of the content of the document.
     */
    public function getContentYSize(): float
    {
        return $this->getContentYEnd() - $this->getContentYStart();
    }

    public function getFooterYStart(): float
    {
        return $this->getContentYEnd() + $this->layout->getBaseSpacing();
    }

    public function getMarginBottom(): float
    {
        return $this->getPageSizeY() - $this->getFooterYStart() + $this->layout->getBaseSpacing();
    }

    public function getMarginRight(): float
    {
        return $this->getPageSizeX() - $this->getContentXEnd();
    }

    public function getColumnGutter(): float
    {
        return $this->layout->getBaseSpacing() / $this->layout->getScalingFactor();
    }

    public function getRegionSpacer(): float
    {
        return $this->layout->getBaseSpacing() * ($this->layout->getScalingFactor() ** 2);
    }

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
