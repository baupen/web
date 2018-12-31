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
    private $document;

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
        $this->document = new Pdf($pdfDefinition, $this->pdfSizes);

        //prepare fonts
        $checkFilePath = K_PATH_FONTS . '/.copied';
        if (!file_exists($checkFilePath)) {
            $sourceFolder = __DIR__ . '/../../../assets/report/fonts';
            //copy all fonts from the assets to the fonts folder of tcpdf
            shell_exec('\cp -r ' . $sourceFolder . '/* ' . K_PATH_FONTS);
            file_put_contents($checkFilePath, time());
        }

        $this->document->AddPage();
        $this->document->SetY($this->pdfSizes->getContentYStart());

        $this->setDefaults();
    }

    private function setDefaults()
    {
        //set typography
        $this->document->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->document->SetFontSize($this->pdfSizes->getTextFontSize());
        $this->document->SetLineWidth($this->pdfSizes->getLineWidth());

        // set colors
        $this->document->SetFillColor(...$this->pdfDesign->getLightBackground());
        $this->document->SetDrawColor(...$this->pdfDesign->getDarkBackground());
        $this->document->SetTextColor(...$this->pdfDesign->getTextColor());

        //set layout
        $this->document->SetLeftMargin($this->pdfSizes->getContentXStart());
        $this->document->setCellPaddings(...$this->pdfSizes->getDefaultCellPadding());

        //set position
        $this->document->SetX($this->pdfSizes->getContentXStart());
        if ($this->document->GetY() > $this->pdfSizes->getContentYEnd()) {
            $this->document->AddPage();
            $this->document->SetY($this->pdfSizes->getContentYStart());
        }
    }

    /**
     * @param null|string $headerImage
     * @param string $name
     * @param string $address
     * @param string $elements
     * @param string[] $filterEntries
     * @param string $filterHeader
     */
    public function addIntroduction(?string $headerImage, string $name, string $address, string $elements, array $filterEntries, string $filterHeader)
    {
        $startY = $this->document->GetY();
        $maxContentHeight = $startY;

        //three or two column layout
        $columnCount = 3;
        $currentColumn = 0;

        //image
        if (file_exists($headerImage)) {
            $maxImageWidth = $this->pdfSizes->getColumnContentWidth($columnCount);
            list($width, $height) = ImageHelper::getWidthHeightArguments($headerImage, $maxImageWidth, $maxImageWidth);
            $this->document->Image($headerImage, $this->pdfSizes->getContentXStart(), $startY, $width, $height);
            $maxContentHeight = max($this->document->GetY() + $height, $maxContentHeight);

            //set position for the next content
            ++$currentColumn;
        } else {
            --$columnCount;
        }

        $columnWidth = $this->pdfSizes->getColumnContentWidth($columnCount);

        //construction site description
        $this->goToStartOfColumn($startY, $currentColumn, $columnCount);

        $this->printH2($name, $columnWidth);
        $this->printP($address, $columnWidth);

        $this->document->Ln($this->pdfSizes->getContentSpacerBig());
        $this->printP($elements, $columnWidth, true);
        $maxContentHeight = max($this->document->GetY(), $maxContentHeight);
        ++$currentColumn;

        //filter used for generation
        $this->goToStartOfColumn($startY, $currentColumn, $columnCount);

        $this->printH2($filterHeader, $columnWidth);

        $this->document->SetFontSize($this->pdfSizes->getTextFontSize());
        foreach ($filterEntries as $name => $value) {
            $this->document->SetX($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
            $this->printHtmlP('<b>' . $name . '</b>: ' . $value);
        }

        //define start of next part
        $this->document->SetY(max($this->document->GetY(), $maxContentHeight));
        $this->endRegion();
    }

    private function goToStartOfColumn($startY, $currentColumn, $columnCount)
    {
        $this->document->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
        $this->document->SetY($startY);
    }

    private function endRegion()
    {
        $this->document->SetY($this->document->GetY() + $this->pdfSizes->getContentSpacerBig());
    }

    private function printH2($text, $columnWidth = 0, $description = '')
    {
        $this->document->SetFontSize($this->pdfSizes->getTitleFontSize());
        $this->document->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->document->MultiCell($columnWidth, 0, $text, 0, 'L', false, 1);

        if (mb_strlen($description) > 0) {
            $this->document->SetFontSize($this->pdfSizes->getTextFontSize());
            $this->document->SetFont(...$this->pdfDesign->getDefaultFontFamily());
            $this->document->MultiCell($columnWidth, 0, $description, 0, 'L', false, 1);
        }

        $this->document->Ln($this->pdfSizes->getLnHeight());
    }

    private function printH3($text, $columnWidth = 0, $description = '')
    {
        $this->document->SetFontSize($this->pdfSizes->getTextFontSize());
        $this->document->SetFont(...$this->pdfDesign->getEmphasisFontFamily());
        $this->document->MultiCell($columnWidth, 0, $text, 0, 'L', false, 1);

        if (mb_strlen($description) > 0) {
            $this->document->SetFontSize($this->pdfSizes->getTextFontSize());
            $this->document->SetFont(...$this->pdfDesign->getDefaultFontFamily());
            $this->document->MultiCell($columnWidth, 0, $description, 0, 'L', false, 1);
        }

        $this->document->Ln($this->pdfSizes->getLnHeight());
    }

    private function printP($text, $columnWidth = 0, $secondary = false)
    {
        if ($secondary) {
            $this->document->SetTextColor(...$this->pdfDesign->getSecondaryTextColor());
        } else {
            $this->document->SetTextColor(...$this->pdfDesign->getTextColor());
        }
        $this->document->SetFontSize($this->pdfSizes->getTextFontSize());
        $this->document->MultiCell($columnWidth, 0, $text, 0, 'L', false, 2);

        $this->document->SetTextColor(...$this->pdfDesign->getTextColor());
    }

    private function printHtmlP($html)
    {
        $this->document->writeHTMLCell(0, 0, $this->document->GetX(), $this->document->GetY(), $html, 0, 1);
        //-2 because the html does not stop at the correct height
        $this->document->SetY($this->document->GetY());
    }

    private function getHeightOf($closure)
    {
        $this->document->startTransaction();
        if ($this->document->GetY() > $this->pdfSizes->getPageSizeY()) {
            $this->document->AddPage();
        }
        $startY = $this->document->GetY();
        $startPage = $this->document->getPage();
        $closure();
        $endY = $this->document->GetY();
        $endPage = $this->document->getPage();
        $this->document = $this->document->rollbackTransaction();
        $pageAdapt = $this->pdfSizes->getContentYSize() * ($endPage - $startPage);

        return $endY - $startY + $pageAdapt;
    }

    /**
     * @param string $targetFilePath
     */
    public function save($targetFilePath)
    {
        $this->document->Output($targetFilePath, 'F');
    }

    /**
     * @param string $name
     * @param null|string $context
     * @param null|string $mapImageFilePath
     */
    public function addImage(string $name, ?string $context, ?string $mapImageFilePath = null)
    {
        $this->setDefaults();
        $startY = $this->document->GetY();

        $printTitle = function () use ($name, $context) {
            $this->endRegion();
            $this->printH2($name, 0, $context);
        };

        $headerHeight = $this->getHeightOf(function () use ($printTitle) {
            $printTitle();
        });

        $imgPadding = $this->pdfSizes->getImagePadding();
        $doubleImgPadding = 2 * $this->pdfSizes->getImagePadding();

        $maxWidth = $this->pdfSizes->getContentXSize() - $doubleImgPadding;
        $maxHeight = $this->pdfSizes->getContentYSize() - $headerHeight - $doubleImgPadding;
        list($width, $height) = ImageHelper::getWidthHeightArguments($mapImageFilePath, $maxWidth, $maxHeight);

        //check if image fits on current page
        if ($headerHeight + $height + $startY + $this->pdfSizes->getContentSpacerBig() + $doubleImgPadding < $this->pdfSizes->getContentYEnd()) {
            //add content spacer & continue on same page
            $this->document->SetY($startY + $this->pdfSizes->getContentSpacerSmall(), true, false);
        } else {
            //force new page
            $this->document->AddPage();
            $this->document->SetY($this->pdfSizes->getContentYStart());
        }

        //print title
        $printTitle();

        //print image with surrounding box
        $startY = $this->document->GetY();
        $this->document->Cell($this->pdfSizes->getContentXSize(), $height + $doubleImgPadding, '', 0, 2, '', true);
        $this->document->SetY($startY);
        $this->document->Image($mapImageFilePath, $this->pdfSizes->getContentXStart() + (((float)$this->pdfSizes->getContentXSize() - $width) / 2), $this->document->GetY() + $imgPadding, $width, $height);

        //adapt Y with spacer for next
        $this->document->SetY($startY + $height + $doubleImgPadding + $this->pdfSizes->getContentSpacerBig());
    }

    /**
     * @param string $name
     * @param null|string $context
     */
    public function addHeader(string $name, ?string $context)
    {
        $this->setDefaults();
        $this->printH2($name, 0, $context);
    }

    /**
     * @param $tableHead
     * @param $tableContent
     * @param string|null $tableTitle
     */
    public function addTable($tableHead, $tableContent, $tableTitle = null)
    {
        $this->setDefaults();

        //print table header
        if ($tableTitle !== null) {
            $this->printH3($tableTitle, $this->pdfSizes->getContentXSize());
        }

        //adapt font for table content
        $this->document->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $this->document->SetFontSize($this->pdfSizes->getTextFontSize());
        $this->document->SetFont(...$this->pdfDesign->getEmphasisFontFamily());

        //make header upper case
        $row = [];
        foreach ($tableHead as $item) {
            $row[] = mb_strtoupper($item, 'UTF-8');
        }

        //print header
        $this->document->SetFillColor(...$this->pdfDesign->getLightBackground());
        $maxTries = 3;
        while (!$this->printRow($row, true, $this->pdfDesign->getLightBackground()) && $maxTries > 0) {
            //simply retry to print row if it did not work
            --$maxTries;
        }

        //print content
        $currentRow = 0;
        $this->document->SetFillColor(...$this->pdfDesign->getLighterBackground());
        $this->document->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        foreach ($tableContent as $row) {
            $maxTries = 3;
            while (!$this->printRow($row, $currentRow % 2 === 1, $this->pdfDesign->getLighterBackground()) && $maxTries > 0) {
                //simply retry to print row if it did not work
                --$maxTries;
            }
            ++$currentRow;
        }

        //define start of next part
        $this->document->SetY($this->document->GetY() + $this->pdfSizes->getContentSpacerBig());
    }

    /**
     * @param $row
     * @param $fill
     * @param $fillBackground
     *
     * @return bool
     */
    private function printRow($row, $fill, $fillBackground)
    {
        //alternative background colors
        $columnCount = \count($row);

        //put columns
        $maxContentHeight = 0;
        $currentColumn = 0;
        $fullWidth = 0;
        $startY = $this->document->GetY();
        $startPage = $this->document->getPage();
        foreach ($row as $item) {
            $this->document->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $startY);
            $currentWidth = $this->pdfSizes->getColumnWidth($currentColumn, $columnCount);

            //draw cell content
            $this->document->MultiCell($currentWidth, $maxContentHeight - $startY, $item, 0, 'L', $fill, 1);

            //if new page started; remove from old page and retry on new page
            if ($this->document->getPage() > $startPage) {
                $newHeight = $this->document->GetY();

                //row did not fit on current page; start over on new page
                //print over started row
                $this->document->setPage($startPage);
                $this->document->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $startY);
                $this->document->SetCellPadding(0);
                $this->document->SetFillColor(...$this->pdfDesign->getWhiteBackground());
                $this->document->Cell($this->pdfSizes->getContentXSize(), $this->pdfSizes->getContentYEnd() - $startY, '', 0, 0, '', true);

                //go to new page
                $this->document->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $this->pdfSizes->getContentYStart());
                $this->document->setPage($startPage + 1);

                //print over started row
                if ($fill) {
                    $this->document->SetFillColor(...$fillBackground);
                }
                $this->document->Cell($this->pdfSizes->getContentXSize(), $newHeight - $this->pdfSizes->getContentYStart(), '', 0, 0, '', true);
                //draw line
                $lineX = $this->pdfSizes->getContentYStart();
                $this->document->Line($this->pdfSizes->getContentXStart(), $lineX, $this->pdfSizes->getContentXEnd(), $lineX);

                //set position to start new row
                $this->document->SetXY($this->pdfSizes->getColumnStart(0, $columnCount) + $this->pdfSizes->getLineWidth(), $lineX);

                //reset colors
                $this->document->SetFillColor(...$fillBackground);
                $this->document->setCellPaddings(...$this->pdfSizes->getTableCellPadding());

                return false;
            }

            //if row is higher now than before; draw background from preceding columns
            if ($maxContentHeight !== 0 && $this->document->GetY() > $maxContentHeight) {
                $diff = $this->document->GetY() - $maxContentHeight;
                $newMaxHeight = $this->document->GetY();

                //redraw fill if needed
                if ($fill) {
                    $this->document->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $maxContentHeight);
                    $this->document->SetCellPadding(0);
                    $this->document->Cell($fullWidth, $diff, '', 0, 0, '', $fill);
                    $this->document->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
                }

                //set position for new row
                $this->document->SetY($newMaxHeight);
                $maxContentHeight += $diff;
            } else {
                $maxContentHeight = $this->document->GetY();
            }
            ++$currentColumn;
            $fullWidth += $currentWidth;
        }

        //draw finishing line & set position for new row
        $this->document->Line($this->pdfSizes->getContentXStart(), $maxContentHeight, $this->pdfSizes->getContentXEnd(), $maxContentHeight);
        $this->document->SetY($maxContentHeight + $this->pdfSizes->getLineWidth());

        return true;
    }

    /**
     * @param array $imageGrid each grid entry must define an imagePath & identification
     * @param int $columnCount
     */
    public function addImageGrid(array $imageGrid, int $columnCount)
    {
        $this->setDefaults();

        $columnWidth = $this->pdfSizes->getColumnContentWidth($columnCount);

        $this->document->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
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
            if ($this->document->GetY() + $rowHeight > $this->pdfSizes->getContentYEnd()) {
                //force new page
                $this->document->AddPage();
                $this->document->SetY($this->pdfSizes->getContentYStart());
            }
            $startY = $this->document->GetY();

            //print images
            $currentColumn = 0;
            foreach ($row as &$entry) {
                //image
                $height = $entry['height'];
                $width = $entry['width'];
                $xStart = $this->pdfSizes->getColumnStart($currentColumn, $columnCount) + (((float)$columnWidth - $width) / 2);
                $this->document->Image($entry['imagePath'], $xStart, $startY, $width, $height, '', '', '', '', 300, '', false, false, 1);

                //identification
                $this->document->SetXY($xStart, $startY);
                $width = mb_strlen((string)$entry['identification']) * $this->pdfSizes->getTextFontSize() / 5 + $cellWidthPadding;
                $this->document->Cell($width, 0, $entry['identification'], 0, 0, '', true);
                ++$currentColumn;
            }

            $this->document->SetY($startY + $rowHeight + $this->pdfSizes->getColumnGutter());
        }
    }
}
