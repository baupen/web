<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Report;

class PdfSizes
{
    /**
     * @var int the used page size
     */
    private $pageSize = [210, 297];

    /**
     * @var int
     */
    private $marginSide = 10;

    /**
     * @var int
     */
    private $marginVerticalOuter = 6;

    /**
     * @var int
     */
    private $headerSize = 8;

    /**
     * @var int
     */
    private $differentContentMargin = 6;

    /**
     * @var int
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
     * @var int
     */
    private $gutterSize = 2;

    /**
     * the total width of the document.
     *
     * @return int
     */
    private function getPageSizeX()
    {
        return $this->pageSize[0];
    }

    /**
     * the total width of the document.
     *
     * @return int
     */
    private function getPageSizeY()
    {
        return $this->pageSize[1];
    }

    /**
     * @return int
     */
    public function getHeaderYStart(): int
    {
        return $this->marginVerticalOuter;
    }

    /**
     * @return int
     */
    public function getHeaderHeight(): int
    {
        return $this->headerSize;
    }

    /**
     * @return int
     */
    public function getContentXStart(): int
    {
        return $this->marginSide;
    }

    /**
     * the width of the document till the right margin.
     *
     * @return int
     */
    public function getContentXEnd()
    {
        return $this->getPageSizeX() - $this->marginSide;
    }

    /**
     * @return int
     */
    public function getContentXSize()
    {
        return $this->getContentXEnd() - $this->getContentXStart();
    }

    /**
     * @return int
     */
    public function getContentYStart(): int
    {
        return $this->getHeaderYStart() + $this->getHeaderHeight() + $this->differentContentMargin;
    }

    /**
     * the width of the document till the right margin.
     *
     * @return int
     */
    public function getContentYEnd()
    {
        return $this->getPageSizeY() - $this->marginVerticalOuter - $this->footerSize - $this->differentContentMargin;
    }

    /**
     * the width of the content of the document.
     *
     * @return int
     */
    public function getContentYSize()
    {
        return $this->getContentYEnd() - $this->getContentYStart();
    }

    /**
     * @return int
     */
    public function getFooterYStart(): int
    {
        return $this->getContentYEnd() + $this->differentContentMargin;
    }

    /**
     * @return int
     */
    public function getColumnGutter()
    {
        return $this->gutterSize;
    }

    /**
     * @param $numberOfColumns
     *
     * @return float|int
     */
    public function getColumnContentWidth($numberOfColumns)
    {
        $gutterSpace = ($numberOfColumns - 1) * $this->getColumnGutter();

        return (float)($this->getContentXSize() - $gutterSpace) / $numberOfColumns;
    }

    /**
     * @param $numberOfColumns
     *
     * @return float|int
     */
    public function getColumnWidth($numberOfColumns)
    {
        return $this->getColumnContentWidth($numberOfColumns) + $this->getColumnGutter();
    }

    /**
     * @param $currentColumn
     * @param $numberOfColumns
     *
     * @return float|int
     */
    public function getColumnStart($currentColumn, $numberOfColumns)
    {
        return ($this->getColumnWidth($numberOfColumns)) * $currentColumn + $this->getContentXStart();
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

    /**
     * gives back the width and height to be passed to tcpdf
     * be aware that if passed 0 tcpdf expands to fill the.
     *
     * @param $imgPath
     * @param $maxWidth
     * @param $maxHeight
     *
     * @return array
     */
    public function getWidthHeightArguments($imgPath, $maxWidth, $maxHeight)
    {
        //get image sizes
        $imageSizes = getimagesize($imgPath);
        $realWidth = $imageSizes[0];
        $realHeight = $imageSizes[1];

        //get ratios
        $widthRatio = $maxWidth / $realWidth;
        $heightRatio = $maxHeight / $realHeight;

        if ($widthRatio < 1 && $heightRatio < 1) {
            //image bigger than box
            if ($widthRatio < $heightRatio) {
                $scale = $widthRatio;
            } else {
                $scale = $heightRatio;
            }
        } elseif ($widthRatio > 1 && $heightRatio > 1) {
            //image smaller than box
            if ($widthRatio > $heightRatio) {
                $scale = $widthRatio;
            } else {
                $scale = $heightRatio;
            }
        } else {
            if ($widthRatio < 1) {
                $scale = $widthRatio;
            } else {
                $scale = $heightRatio;
            }
        }

        return [$realWidth * $scale, $realHeight * $scale];
    }

    /**
     * @return int
     */
    public function getContentSpacerBig(): int
    {
        return $this->differentContentMargin;
    }

    /**
     * @return int
     */
    public function getContentSpacerSmall(): int
    {
        return $this->differentContentMargin / $this->scalingFactor ** 2;
    }

    /**
     * @return float[]
     */
    public function getDefaultCellPadding(): array
    {
        return [0, 1, 0, 1];
    }

    /**
     * @return float[]
     */
    public function getTableCellPadding(): array
    {
        return [$this->scalingFactor, 1, $this->scalingFactor, 1];
    }
}
