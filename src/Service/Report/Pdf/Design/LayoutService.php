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

use App\Service\Report\Pdf\Design\Interfaces\LayoutServiceInterface;

class LayoutService implements LayoutServiceInterface
{
    /**
     * the total width of the document.
     *
     * @return float
     */
    private function getPageSizeX()
    {
        return $this->pageSize[0];
    }

    /**
     * the total width of the document.
     *
     * @return float
     */
    public function getPageSizeY()
    {
        return $this->pageSize[1];
    }

    /**
     * @return float
     */
    public function getHeaderYStart(): float
    {
        return $this->pageMargins[1];
    }

    /**
     * @return float
     */
    public function getHeaderHeight(): float
    {
        return $this->getHeaderFontSize();
    }

    /**
     * @return float
     */
    public function getContentXStart(): float
    {
        return $this->pageMargins[0];
    }

    /**
     * the width of the document till the right margin.
     *
     * @return float
     */
    public function getContentXEnd(): float
    {
        return $this->getPageSizeX() - $this->pageMargins[2];
    }

    /**
     * @return float
     */
    public function getContentXSize()
    {
        return $this->getContentXEnd() - $this->getContentXStart();
    }

    /**
     * @return float
     */
    public function getContentYStart(): float
    {
        return $this->getHeaderYStart() + $this->getHeaderHeight() + $this->baseSpacing;
    }

    /**
     * the width of the document till the bottom margin.
     *
     * @return float
     */
    public function getContentYEnd()
    {
        return $this->getPageSizeY() - $this->pageMargins[3] - $this->getFooterFontSize() - $this->baseSpacing;
    }

    /**
     * the width of the content of the document.
     *
     * @return float
     */
    public function getContentYSize()
    {
        return $this->getContentYEnd() - $this->getContentYStart();
    }

    /**
     * @return float
     */
    public function getFooterYStart(): float
    {
        return $this->getContentYEnd() + $this->baseSpacing;
    }

    /**
     * @return float
     */
    public function getMarginBottom(): float
    {
        return $this->getPageSizeY() - $this->getFooterYStart() + $this->baseSpacing;
    }

    /**
     * @return float
     */
    public function getColumnGutter()
    {
        return $this->baseSpacing / $this->scalingFactor;
    }

    /**
     * @param $numberOfColumns
     *
     * @return float|float
     */
    public function getColumnContentWidth($numberOfColumns)
    {
        $gutterSpace = ($numberOfColumns - 1) * $this->getColumnGutter();

        return (float)($this->getContentXSize() - $gutterSpace) / $numberOfColumns;
    }

    /**
     * @param $currentColumn
     * @param $numberOfColumns
     *
     * @return float|float
     */
    public function getColumnWidth($currentColumn, $numberOfColumns)
    {
        $baseWidth = $this->getColumnContentWidth($numberOfColumns);
        if ($currentColumn === $numberOfColumns - 1) {
            return $baseWidth;
        }

        return $baseWidth + $this->getColumnGutter();
    }

    /**
     * @param $currentColumn
     * @param $numberOfColumns
     *
     * @return float|float
     */
    public function getColumnStart($currentColumn, $numberOfColumns)
    {
        return ($this->getColumnWidth($currentColumn - 1, $numberOfColumns)) * $currentColumn + $this->getContentXStart();
    }
}
