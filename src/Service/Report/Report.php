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
        $checkFilePath = K_PATH_FONTS.'/.copied2';
        if (!file_exists($checkFilePath)) {
            $sourceFolder = __DIR__.'/../../../../assets/report/fonts';
            //copy all fonts from the assets to the fonts folder of tcpdf
            shell_exec('\cp -r '.$sourceFolder.'/* '.K_PATH_FONTS);
            file_put_contents($checkFilePath, time());
        }

        $this->pdfDocument->AddPage();
        $this->pdfDocument->SetY($this->pdfSizes->getContentYStart());

        $this->setDefaults();
    }

    /**
     * @param string[] $filterEntries
     */
    public function addIntroduction(?string $headerImage, string $name, string $address, string $elements, array $filterEntries, string $filterHeader)
    {
        $startY = $this->pdfDocument->GetY();
        $maxContentHeight = $startY;

        //three or two column layout
        $columnCount = 3;
        $currentColumn = 0;

        //image
        if (file_exists($headerImage)) {
            $maxImageWidth = $this->pdfSizes->getColumnContentWidth($columnCount);
            list($width, $height) = ImageHelper::fitInBoundingBox($headerImage, $maxImageWidth, $maxImageWidth);
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

        $this->pdfDocument->Ln($this->pdfSizes->getLnHeight() * M_PI);
        $this->printP($elements, $columnWidth, true);
        $maxContentHeight = max($this->pdfDocument->GetY(), $maxContentHeight);
        ++$currentColumn;

        //filter used for generation
        if (\count($filterEntries) > 0) {
            $this->pdfDocument->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
            $this->pdfDocument->SetY($startY);

            $this->printH2($filterHeader, $columnWidth);

            $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
            foreach ($filterEntries as $name => $value) {
                $this->pdfDocument->SetX($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
                $this->printHtmlP('<b>'.$name.'</b>: '.$value);
            }
        }

        //define start of next part
        $this->pdfDocument->SetY(max($this->pdfDocument->GetY(), $maxContentHeight) + $this->pdfSizes->getContentSpacerBig());
    }

    /**
     * @param string $targetFilePath
     */
    public function save($targetFilePath)
    {
        $this->pdfDocument->Output($targetFilePath, 'F');
    }

    public function addMap(string $name, ?string $context, ?string $mapImageFilePath = null)
    {
        $this->pdfDocument->startPage();

        $this->setDefaults();
        $startY = $this->pdfDocument->GetY();

        $printTitle = function () use ($name, $context) {
            $this->pdfDocument->SetY($this->pdfDocument->GetY() + $this->pdfSizes->getContentSpacerBig());
            $this->printH2($name, 0, $context);
        };

        $headerHeight = $this->getHeightOf(function () use ($printTitle) {
            $printTitle();
        });

        if (file_exists($mapImageFilePath)) {
            $imgPadding = $this->pdfSizes->getImagePadding();
            $doubleImgPadding = 2 * $this->pdfSizes->getImagePadding();

            $maxWidth = $this->pdfSizes->getContentXSize() - $doubleImgPadding;
            $maxHeight = $this->pdfSizes->getContentYSize() - $headerHeight - $doubleImgPadding;
            list($width, $height) = ImageHelper::fitInBoundingBox($mapImageFilePath, $maxWidth, $maxHeight);

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
            $printTitle();

            //print image with surrounding box
            $startY = $this->pdfDocument->GetY();
            $this->pdfDocument->Cell($this->pdfSizes->getContentXSize(), $height + $doubleImgPadding, '', 0, 2, '', true);
            $this->pdfDocument->SetY($startY);
            $this->pdfDocument->Image($mapImageFilePath, $this->pdfSizes->getContentXStart() + (((float) $this->pdfSizes->getContentXSize() - $width) / 2), $this->pdfDocument->GetY() + $imgPadding, $width, $height);

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
     * @param string|null $tableTitle
     * @param mixed|null  $firstColumnSize
     */
    public function addTable($tableHead, $tableContent, $tableTitle = null, $firstColumnSize = null)
    {
        $this->setDefaults();

        //print table header
        if (null !== $tableTitle) {
            $this->printH3($tableTitle, $this->pdfSizes->getContentXSize());
        }

        //adapt font for table content
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getEmphasisFontFamily());

        //make header upper case
        $row = [];
        foreach ($tableHead as $item) {
            $row[] = mb_strtoupper($item, 'UTF-8');
        }

        //print header
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLightBackground());
        $maxTries = 3;
        while (!$this->printRow($row, true, $this->pdfDesign->getLightBackground(), $firstColumnSize) && $maxTries > 0) {
            //simply retry to print row if it did not work
            --$maxTries;
        }

        //print content
        $currentRow = 0;
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLighterBackground());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        foreach ($tableContent as $row) {
            $maxTries = 3;
            while (!$this->printRow($row, 1 === $currentRow % 2, $this->pdfDesign->getLighterBackground(), $firstColumnSize) && $maxTries > 0) {
                //simply retry to print row if it did not work
                --$maxTries;
            }
            ++$currentRow;
        }

        //define start of next part
        $this->pdfDocument->SetY($this->pdfDocument->GetY() + $this->pdfSizes->getContentSpacerBig());
    }

    /**
     * @param array $imageGrid each grid entry must define an imagePath & identification
     */
    public function addImageGrid(array $imageGrid, int $columnCount)
    {
        $this->setDefaults();

        $columnWidth = $this->pdfSizes->getColumnContentWidth($columnCount);

        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $cellWidthPadding = $this->pdfSizes->getTableCellPadding()[0] + $this->pdfSizes->getTableCellPadding()[2];
        foreach ($imageGrid as $row) {
            //get row height & calculate the other sizes
            $rowHeight = 0;
            foreach ($row as &$entry) {
                $imagePath = $entry['imagePath'];

                list($width, $height) = ImageHelper::fitInBoundingBox($imagePath, $columnWidth, $columnWidth);
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
            foreach ($row as &$entry) {
                //image
                $height = $entry['height'];
                $width = $entry['width'];
                $xStart = $this->pdfSizes->getColumnStart($currentColumn, $columnCount);
                $this->pdfDocument->Image($entry['imagePath'], $xStart, $startY, $width, $height, '', '', '', '', 300, '', false, false, 1);

                //identification
                $this->pdfDocument->SetXY($xStart, $startY);
                $width = mb_strlen((string) $entry['identification']) * $this->pdfSizes->getRegularFontSize() / 5 + $cellWidthPadding;
                $this->pdfDocument->Cell($width, 0, $entry['identification'], 0, 0, '', true);
                ++$currentColumn;
            }

            $this->pdfDocument->SetY($startY + $rowHeight + $this->pdfSizes->getColumnGutter());
        }
    }

    private function setDefaults()
    {
        //set typography
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->SetLineWidth($this->pdfSizes->getLineWidth());

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

    private function printP($text, $columnWidth = 0, $secondary = false)
    {
        if ($secondary) {
            $this->pdfDocument->SetTextColor(...$this->pdfDesign->getSecondaryTextColor());
        } else {
            $this->pdfDocument->SetTextColor(...$this->pdfDesign->getTextColor());
        }
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 2);

        $this->pdfDocument->SetTextColor(...$this->pdfDesign->getTextColor());
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

    private function printRow($row, $fill, $fillBackground, $firstColumnSize = null)
    {
        //alternative background colors
        $columnCount = \count($row);

        //put columns
        $maxContentHeight = 0;
        $currentColumn = 0;
        $fullWidth = 0;
        $startY = $this->pdfDocument->GetY();
        $startPage = $this->pdfDocument->getPage();
        $this->pdfDocument->startTransaction();
        foreach ($row as $item) {
            $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount, $firstColumnSize), $startY);
            $currentWidth = $this->pdfSizes->getColumnWidth($currentColumn, $columnCount, $firstColumnSize);

            //draw cell content
            $this->pdfDocument->MultiCell($currentWidth, $maxContentHeight - $startY, $item, 0, 'L', $fill, 1);

            //if new page started; remove from old page and retry on new page
            if ($this->pdfDocument->getPage() > $startPage) {
                $this->pdfDocument->rollbackTransaction(true);

                $this->pdfDocument->AddPage();

                $lineX = $this->pdfSizes->getContentYStart();
                $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $lineX, $this->pdfSizes->getContentXEnd(), $lineX);

                $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart(0, $columnCount) + $this->pdfSizes->getLineWidth(), $lineX);

                return false;
            }

            //if row is higher now than before; draw background from preceding columns
            if (0 !== $maxContentHeight && $this->pdfDocument->GetY() > $maxContentHeight) {
                $diff = $this->pdfDocument->GetY() - $maxContentHeight;
                $newMaxHeight = $this->pdfDocument->GetY();

                //redraw fill if needed
                if ($fill) {
                    $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $maxContentHeight, $firstColumnSize);
                    $this->pdfDocument->SetCellPadding(0);
                    $this->pdfDocument->Cell($fullWidth, $diff, '', 0, 0, '', $fill);
                    $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
                }

                //set position for new row
                $this->pdfDocument->SetY($newMaxHeight);
                $maxContentHeight += $diff;
            } else {
                $maxContentHeight = $this->pdfDocument->GetY();
            }
            ++$currentColumn;
            $fullWidth += $currentWidth;
        }

        //draw finishing line & set position for new row
        $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $maxContentHeight, $this->pdfSizes->getContentXEnd(), $maxContentHeight);
        $this->pdfDocument->SetY($maxContentHeight + $this->pdfSizes->getLineWidth());

        $this->pdfDocument->commitTransaction();

        return true;
    }
}
