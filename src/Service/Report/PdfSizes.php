<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report;

class PdfSizes
{
    /**
     * @var float the used page size
     */
    private $pageSize = [210, 297];

    /**
     * @var float[]
     *              margins of the page; left, top, right, bottom
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
     * @var float
     */
    private $baseFontSize = 8;

    /**
     * @var float
     */
    private $lineWidth = 0.2;

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

    /**
     * for footers/headers.
     *
     * @return float
     */
    private function getSmallFontSize()
    {
        return $this->getRegularFontSize() / $this->scalingFactor;
    }

    /**
     * for text.
     *
     * @return float
     */
    private function getRegularFontSize()
    {
        return $this->baseFontSize;
    }

    /**
     * for text.
     *
     * @return float
     */
    private function getBigFontSize()
    {
        return $this->getRegularFontSize() * $this->scalingFactor;
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

    /**
     * @return float
     */
    public function getLnHeight()
    {
        return $this->scalingFactor;
    }

    /**
     * @return float
     */
    public function getContentSpacerBig(): float
    {
        return $this->baseSpacing;
    }

    /**
     * @return float
     */
    public function getContentSpacerSmall(): float
    {
        return (float)$this->baseSpacing / $this->scalingFactor ** 2;
    }

    /**
     * @return float[]
     */
    public function getDefaultCellPadding(): array
    {
        return [0, 0, 0, 0];
    }

    /**
     * @return float[]
     */
    public function getTableCellPadding(): array
    {
        return [$this->scalingFactor, 1, $this->scalingFactor, 1];
    }

    /**
     * @return float
     */
    public function getImagePadding(): float
    {
        return $this->getContentSpacerSmall() / 4;
    }

    /**
     * @return float
     */
    public function getLineWidth(): float
    {
        return $this->lineWidth;
    }
}
