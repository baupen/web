<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf;

class PdfSizes
{
    /**
     * @var float the used page size
     */
    private array $pageSize = [210, 297];

    private int $marginSide = 10;

    private int $marginVerticalOuter = 6;

    private int $headerSize = 8;

    private int $differentContentMargin = 6;

    private int $footerSize = 4;

    private int $baseFontSizes = 8;

    private float $scalingFactor = 1.6;

    private int $gutterSize = 4;

    private float $lineWidth = 0.2;

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

    public function getContentXSize(): float
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
    public function getContentYEnd(): int|float
    {
        return $this->getPageSizeY() - $this->marginVerticalOuter - $this->footerSize - $this->differentContentMargin;
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
    public function getColumnGutter($compact = false): int|float
    {
        if ($compact) {
            return $this->gutterSize / 2;
        }

        return $this->gutterSize;
    }

    /**
     * @param mixed|null $firstColumnSize
     */
    public function getColumnContentWidth($numberOfColumns, $firstColumnSize = null): float
    {
        $gutterSpace = ($numberOfColumns - 1) * $this->getColumnGutter();

        if (null !== $firstColumnSize) {
            return ($this->getContentXSize() - $gutterSpace - $firstColumnSize) / ($numberOfColumns - 1);
        }

        return ($this->getContentXSize() - $gutterSpace) / $numberOfColumns;
    }

    /**
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
     * @param mixed|null $firstColumnSize
     */
    public function getColumnStart($currentColumn, $numberOfColumns, $firstColumnSize = null): float
    {
        if (0 === $currentColumn) {
            return $this->getContentXStart();
        }

        if (null !== $firstColumnSize) {
            return $this->getColumnWidth($currentColumn - 1, $numberOfColumns, $firstColumnSize) * ($currentColumn - 1) + $firstColumnSize + $this->getContentXStart();
        }

        return $this->getColumnWidth($currentColumn - 1, $numberOfColumns) * $currentColumn + $this->getContentXStart();
    }

    /**
     * for footers/headers.
     *
     * @return float
     */
    public function getSmallFontSize(): int|float
    {
        return $this->getRegularFontSize() / $this->scalingFactor;
    }

    /**
     * for text.
     */
    public function getRegularFontSize(): int
    {
        return $this->baseFontSizes;
    }

    /**
     * for titles.
     *
     * @return float
     */
    public function getBigFontSize(): int|float
    {
        return $this->baseFontSizes * $this->scalingFactor;
    }

    /**
     * for big headers.
     *
     * @return float
     */
    public function getLargeFontSize(): float|int
    {
        return $this->baseFontSizes * ($this->scalingFactor ** 2);
    }

    public function getLnHeight(): float
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

    public function getImageBorder(): float
    {
        return 1;
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
