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
     * @var float
     */
    private $marginSide = 10;

    /**
     * @var float
     */
    private $marginVerticalOuter = 6;

    /**
     * @var float
     */
    private $headerSize = 8;

    /**
     * @var float
     */
    private $differentContentMargin = 6;

    /**
     * @var float
     */
    private $footerSize = 4;

    /**
     * @var float
     */
    private $baseFontSizes = 8;

    /**
     * @var float
     */
    private $scalingFactor = 1.6;

    /**
     * @var float
     */
    private $gutterSize = 4;

    /**
     * @var float
     */
    private $lineWidth = 0.2;

    /**
     * the total width of the document.
     *
     * @return float
     */
    public function getPageSizeY()
    {
        return $this->pageSize[1];
    }

    public function getHeaderYStart(): float
    {
        return $this->marginVerticalOuter;
    }

    public function getHeaderHeight(): float
    {
        return $this->headerSize;
    }

    public function getContentXStart(): float
    {
        return $this->marginSide;
    }

    /**
     * the width of the document till the right margin.
     */
    public function getContentXEnd(): float
    {
        return $this->getPageSizeX() - $this->marginSide;
    }

    /**
     * @return float
     */
    public function getContentXSize()
    {
        return $this->getContentXEnd() - $this->getContentXStart();
    }

    public function getContentYStart(): float
    {
        return $this->getHeaderYStart() + $this->getHeaderHeight() + $this->differentContentMargin;
    }

    /**
     * the width of the document till the right margin.
     *
     * @return float
     */
    public function getContentYEnd()
    {
        return $this->getPageSizeY() - $this->marginVerticalOuter - $this->footerSize - $this->differentContentMargin;
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

    public function getFooterYStart(): float
    {
        return $this->getContentYEnd() + $this->differentContentMargin;
    }

    public function getMarginBottom(): float
    {
        return $this->getPageSizeY() - $this->getFooterYStart() + $this->differentContentMargin;
    }

    /**
     * @param bool $compact
     *
     * @return float
     */
    public function getColumnGutter($compact = false)
    {
        if ($compact) {
            return $this->gutterSize / 2;
        }

        return $this->gutterSize;
    }

    /**
     * @param $numberOfColumns
     * @param bool       $compact
     * @param mixed|null $firstColumnSize
     *
     * @return float|float
     */
    public function getColumnContentWidth($numberOfColumns, $firstColumnSize = null)
    {
        $gutterSpace = ($numberOfColumns - 1) * $this->getColumnGutter();

        if (null !== $firstColumnSize) {
            return (float) ($this->getContentXSize() - $gutterSpace - $firstColumnSize) / ($numberOfColumns - 1);
        }

        return (float) ($this->getContentXSize() - $gutterSpace) / $numberOfColumns;
    }

    /**
     * @param $currentColumn
     * @param $numberOfColumns
     * @param bool       $compact
     * @param mixed|null $firstColumnSize
     *
     * @return float|float
     */
    public function getColumnWidth($currentColumn, $numberOfColumns, $firstColumnSize = null)
    {
        if (null !== $firstColumnSize && 0 === $currentColumn) {
            return $firstColumnSize;
        }

        $baseWidth = $this->getColumnContentWidth($numberOfColumns, $firstColumnSize);

        return $baseWidth + $this->getColumnGutter();
    }

    /**
     * @param $currentColumn
     * @param $numberOfColumns
     * @param bool       $compact
     * @param mixed|null $firstColumnSize
     *
     * @return float|float
     */
    public function getColumnStart($currentColumn, $numberOfColumns, $firstColumnSize = null)
    {
        if (0 === $currentColumn) {
            return $this->getContentXStart();
        }

        if (null !== $firstColumnSize) {
            return ($this->getColumnWidth($currentColumn - 1, $numberOfColumns, $firstColumnSize)) * ($currentColumn - 1) + $firstColumnSize + $this->getContentXStart();
        }

        return ($this->getColumnWidth($currentColumn - 1, $numberOfColumns)) * $currentColumn + $this->getContentXStart();
    }

    /**
     * for footers/headers.
     *
     * @return float
     */
    public function getSmallFontSize()
    {
        return $this->getRegularFontSize() / $this->scalingFactor;
    }

    /**
     * for text.
     *
     * @return float
     */
    public function getRegularFontSize()
    {
        return $this->baseFontSizes;
    }

    /**
     * for titles.
     *
     * @return float
     */
    public function getBigFontSize()
    {
        return $this->baseFontSizes * $this->scalingFactor;
    }

    /**
     * for big headers.
     *
     * @return float
     */
    public function getLargeFontSize()
    {
        return $this->baseFontSizes * ($this->scalingFactor ** 2);
    }

    /**
     * @return float
     */
    public function getLnHeight()
    {
        return $this->scalingFactor;
    }

    public function getContentSpacerBig(): float
    {
        return $this->differentContentMargin;
    }

    public function getContentSpacerSmall(): float
    {
        return (float) $this->differentContentMargin / $this->scalingFactor ** 2;
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

    public function getImagePadding(): float
    {
        return $this->getContentSpacerSmall() / 4;
    }

    public function getLineWidth(): float
    {
        return $this->lineWidth;
    }

    /**
     * the total width of the document.
     *
     * @return float
     */
    private function getPageSizeX()
    {
        return $this->pageSize[0];
    }
}
