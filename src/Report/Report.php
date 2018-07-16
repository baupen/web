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

use App\Helper\ImageHelper;

class Report
{
    /**
     * @var Pdf
     */
    private $pdfDocument;

    /**
     * @var PdfSizes
     */
    private $pdfSizes;

    /**
     * @var PdfDesign
     */
    private $pdfDesign;

    public function __construct(PdfDefinition $pdfDefinition)
    {
        $this->pdfSizes = new PdfSizes();
        $this->pdfDesign = new PdfDesign();
        $this->pdfDocument = new Pdf($pdfDefinition, $this->pdfSizes);

        //prepare fonts
        $checkFilePath = K_PATH_FONTS . '/.copied2';
        if (!file_exists($checkFilePath)) {
            $sourceFolder = __DIR__ . '/../../assets/fonts/tcpdf';
            //copy all fonts from the assets to the fonts folder of tcpdf
            shell_exec('\cp -r ' . $sourceFolder . '/* ' . K_PATH_FONTS);
            file_put_contents($checkFilePath, time());
        }

        $this->pdfDocument->AddPage();
        $this->pdfDocument->SetY($this->pdfSizes->getContentYStart());

        $this->setDefaults();
    }

    private function setDefaults()
    {
        //set typography
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());

        // set colors
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLightBackground());
        $this->pdfDocument->SetDrawColor(...$this->pdfDesign->getDarkBackground());
        $this->pdfDocument->SetTextColor(...$this->pdfDesign->getTextColor());

        //set layout
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getContentXStart());
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getDefaultCellPadding());

        //set position
        $this->pdfDocument->SetX($this->pdfSizes->getContentXStart());
        if ($this->pdfDocument->GetY() > $this->pdfSizes->getContentYEnd()) {
            $this->pdfDocument->AddPage();
            $this->pdfDocument->SetY($this->pdfSizes->getContentYStart());
        }
    }

    /**
     * @param null|string $headerImage
     * @param string $name
     * @param string $address
     * @param string[] $filterEntries
     * @param string $filterHeader
     */
    public function addIntroduction(?string $headerImage, string $name, string $address, array $filterEntries, string $filterHeader)
    {
        $startY = $this->pdfDocument->GetY();
        $maxContentHeight = $startY;

        //three or two column layout
        $columnCount = 3;
        $currentColumn = 0;

        //image
        if (file_exists($headerImage)) {
            $maxImageWidth = $this->pdfSizes->getColumnContentWidth($columnCount);
            list($width, $height) = ImageHelper::getWidthHeightArguments($headerImage, $maxImageWidth, $maxImageWidth);
            $this->pdfDocument->Image($headerImage, $this->pdfSizes->getContentXStart(), $startY, $width, $height);
            $maxContentHeight = max($this->pdfDocument->GetY() + $height, $maxContentHeight);

            //set position for the next content
            ++$currentColumn;
        } else {
            --$columnCount;
        }

        $columnWidth = $this->pdfSizes->getColumnContentWidth($columnCount);

        //construction site description
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
        $this->pdfDocument->SetY($startY);

        $this->printH2($name, $columnWidth);

        $this->printP($address, $columnWidth);
        $maxContentHeight = max($this->pdfDocument->GetY(), $maxContentHeight);
        ++$currentColumn;

        //filter used for generation
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
        $this->pdfDocument->SetY($startY);

        $this->printH2($filterHeader, $columnWidth);

        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        foreach ($filterEntries as $name => $value) {
            $this->pdfDocument->SetX($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
            $this->printHtmlP('<b>' . $name . '</b>: ' . $value);
        }

        //define start of next part
        $this->pdfDocument->SetY(max($this->pdfDocument->GetY(), $maxContentHeight) + $this->pdfSizes->getContentSpacerBig());
    }

    private function printH2($text, $columnWidth = 0, $description = '')
    {
        $this->pdfDocument->SetFontSize($this->pdfSizes->getBigFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 1);

        if (mb_strlen($description) > 0) {
            $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
            $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
            $this->pdfDocument->MultiCell($columnWidth, 0, $description, 0, 'L', false, 1);
        }

        $this->pdfDocument->Ln($this->pdfSizes->getLnHeight());
    }

    private function printH3($text, $columnWidth = 0, $description = '')
    {
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getEmphasisFontFamily());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 1);

        if (mb_strlen($description) > 0) {
            $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
            $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
            $this->pdfDocument->MultiCell($columnWidth, 0, $description, 0, 'L', false, 1);
        }
        $this->pdfDocument->Ln($this->pdfSizes->getLnHeight());
    }

    private function printP($text, $columnWidth = 0)
    {
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 2);
    }

    private function printHtmlP($html)
    {
        $this->pdfDocument->writeHTMLCell(0, 0, $this->pdfDocument->GetX(), $this->pdfDocument->GetY(), $html, 0, 1);
        //-2 because the html does not stop at the correct height
        $this->pdfDocument->SetY($this->pdfDocument->GetY());
    }

    private function getHeightOf($closure)
    {
        $this->pdfDocument->startTransaction();
        if ($this->pdfDocument->GetY() > $this->pdfSizes->getPageSizeY()) {
            $this->pdfDocument->AddPage();
        }
        $startY = $this->pdfDocument->GetY();
        $startPage = $this->pdfDocument->getPage();
        $closure();
        $endY = $this->pdfDocument->GetY();
        $endPage = $this->pdfDocument->getPage();
        $this->pdfDocument = $this->pdfDocument->rollbackTransaction();
        $pageAdapt = $this->pdfSizes->getContentYSize() * ($endPage - $startPage);

        return $endY - $startY + $pageAdapt;
    }

    /**
     * @param string $targetFilePath
     */
    public function save($targetFilePath)
    {
        $this->pdfDocument->Output($targetFilePath, 'F');
    }

    /**
     * @param string $name
     * @param null|string $context
     * @param null|string $mapImageFilePath
     */
    public function addMap(string $name, ?string $context, ?string $mapImageFilePath = null)
    {
        $this->setDefaults();
        $startY = $this->pdfDocument->GetY();

        $headerHeight = $this->getHeightOf(function () use ($name, $context) {
            $this->printH2($name, 0, $context);
        });

        if (file_exists($mapImageFilePath)) {
            $imgPadding = $this->pdfSizes->getImagePadding();
            $doubleImgPadding = 2 * $this->pdfSizes->getImagePadding();

            $maxWidth = $this->pdfSizes->getContentXSize() - $doubleImgPadding;
            $maxHeight = $this->pdfSizes->getContentYSize() - $headerHeight - $doubleImgPadding;
            list($width, $height) = ImageHelper::getWidthHeightArguments($mapImageFilePath, $maxWidth, $maxHeight);

            //check if image fits on current page
            if ($headerHeight + $height + $startY + $this->pdfSizes->getContentSpacerBig() + $doubleImgPadding < $this->pdfSizes->getContentYEnd()) {
                //add content spacer & continue on same page
                $this->pdfDocument->SetY($startY + $this->pdfSizes->getContentSpacerSmall(), true, false);
            } else {
                //force new page
                $this->pdfDocument->AddPage();
                $this->pdfDocument->SetY($this->pdfSizes->getContentYStart());
            }

            //print title
            $this->printH2($name, 0, $context);

            //print image with surrounding box
            $startY = $this->pdfDocument->GetY();
            $this->pdfDocument->Cell($this->pdfSizes->getContentXSize(), $height + $doubleImgPadding, '', 0, 2, '', true);
            $this->pdfDocument->SetY($startY);
            $this->pdfDocument->Image($mapImageFilePath, $this->pdfSizes->getContentXStart() + (((float)$this->pdfSizes->getContentXSize() - $width) / 2), $this->pdfDocument->GetY() + $imgPadding, $width, $height);

            //adapt Y with spacer for next
            $this->pdfDocument->SetY($startY + $height + $doubleImgPadding + $this->pdfSizes->getContentSpacerBig());
        } else {
            //only print title
            $this->printH2($name, 0, $context);
        }
    }

    /**
     * @param $tableHead
     * @param $tableContent
     * @param null $tableTitle
     */
    public function addTable($tableHead, $tableContent, $tableTitle = null)
    {
        $this->setDefaults();

        if ($tableTitle !== null) {
            //table header
            $this->printH3($tableTitle, $this->pdfSizes->getContentXSize());
        }

        //table needs to know where to start
        $startY = $this->pdfDocument->GetY();

        //adapt font for table content
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getEmphasisFontFamily());

        //go through header
        $currentColumn = 0;
        $columnCount = count($tableHead);
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLightBackground());
        foreach ($tableHead as $item) {
            $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $startY);
            $this->pdfDocument->MultiCell($this->pdfSizes->getColumnWidth($currentColumn, $columnCount), 0, mb_strtoupper($item), 0, 'L', true, 1);
            ++$currentColumn;
        }

        $currentRow = 0;
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLighterBackground());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        foreach ($tableContent as $row) {
            //alternative background colors
            $fill = $currentRow % 2;

            //put columns
            $maxContentHeight = 0;
            $currentColumn = 0;
            $fullWidth = 0;
            $startY = $this->pdfDocument->GetY();
            foreach ($row as $item) {
                $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $startY);
                $currentWidth = $this->pdfSizes->getColumnWidth($currentColumn, $columnCount);

                $this->pdfDocument->MultiCell($currentWidth, $maxContentHeight - $startY, $item, 0, 'L', $fill, 1);

                if ($maxContentHeight !== 0 && $this->pdfDocument->GetY() > $maxContentHeight) {
                    $diff = $this->pdfDocument->GetY() - $maxContentHeight;
                    $newMaxHeight = $this->pdfDocument->GetY();
                    $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $maxContentHeight);
                    $this->pdfDocument->SetCellPadding(0);
                    $this->pdfDocument->Cell($fullWidth, $diff - 2, '', 0, 0, '', $fill);
                    $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());

                    $this->pdfDocument->SetY($newMaxHeight);
                    $maxContentHeight += $diff;
                } else {
                    $maxContentHeight = $this->pdfDocument->GetY();
                }
                ++$currentColumn;
                $fullWidth += $currentWidth;
            }
            $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $maxContentHeight, $this->pdfSizes->getContentXEnd(), $maxContentHeight);
            $this->pdfDocument->SetY($maxContentHeight);
            ++$currentRow;
        }

        //define start of next part
        $this->pdfDocument->SetY($this->pdfDocument->GetY() + $this->pdfSizes->getContentSpacerBig());
    }

    /**
     * @param array $imageGrid each grid entry must define an imagePath & identification
     * @param int $columnCount
     */
    public function addImageGrid(array $imageGrid, int $columnCount)
    {
        $this->setDefaults();

        $columnWidth = $this->pdfSizes->getColumnContentWidth($columnCount, true);

        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $cellWidthPadding = $this->pdfSizes->getTableCellPadding()[0] + $this->pdfSizes->getTableCellPadding()[2];
        foreach ($imageGrid as $row) {
            //get row height & calculate the other sizes
            $rowHeight = 0;
            foreach ($row as &$entry) {
                $imagePath = $entry['imagePath'];

                list($width, $height) = ImageHelper::getWidthHeightArguments($imagePath, $columnWidth, $columnWidth);
                $rowHeight = max($rowHeight, $height);
                $entry['width'] = $width;
                $entry['height'] = $height;
            }

            //check if image fits on current page
            if ($this->pdfDocument->GetY() + $rowHeight + $this->pdfSizes->getColumnGutter() > $this->pdfSizes->getContentYEnd()) {
                //force new page
                $this->pdfDocument->AddPage();
                $this->pdfDocument->SetY($this->pdfSizes->getContentYStart());
            }
            $startY = $this->pdfDocument->GetY();

            //print images
            $currentColumn = 0;
            foreach ($row as $entry) {
                //image
                $height = $entry['height'];
                $width = $entry['width'];
                $xStart = $this->pdfSizes->getColumnStart($currentColumn, $columnCount, true) + (((float)$columnWidth - $width) / 2);
                $this->pdfDocument->Image($entry['imagePath'], $xStart, $startY, $width, $height, '', '', '', '', 300, '', false, false, 1);

                //identification
                $this->pdfDocument->SetXY($xStart, $startY);
                $width = mb_strlen((string)$entry['identification']) * $this->pdfSizes->getRegularFontSize() / 5 + $cellWidthPadding;
                $this->pdfDocument->Cell($width, 0, $entry['identification'], 0, 0, '', true);
                ++$currentColumn;
            }

            $this->pdfDocument->SetY($startY + $rowHeight + $this->pdfSizes->getColumnGutter());
        }
    }
}
