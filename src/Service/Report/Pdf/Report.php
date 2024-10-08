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

use App\Helper\ImageHelper;

class Report
{
    private Pdf $pdfDocument;

    private PdfSizes $pdfSizes;

    private PdfDesign $pdfDesign;

    public function __construct(PdfDefinition $pdfDefinition, string $reportAssetDir)
    {
        $this->pdfSizes = new PdfSizes();
        $this->pdfDesign = new PdfDesign();
        $this->pdfDocument = new Pdf($pdfDefinition, $this->pdfSizes);

        // prepare fonts
        $checkFilePath = K_PATH_FONTS.'/.copied';
        if (!file_exists($checkFilePath)) {
            $sourceFolder = $reportAssetDir.'/fonts';
            // copy all fonts from the assets to the fonts folder of tcpdf
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
    public function addIntroduction(?string $headerImage, string $name, string $address, string $elements, array $filterEntries, string $filterHeader): void
    {
        $startY = $this->pdfDocument->GetY();
        $maxContentHeight = $startY;

        // three or two column layout
        $columnCount = 3;
        $currentColumn = 0;

        // image
        if (file_exists($headerImage)) {
            $maxImageWidth = $this->pdfSizes->getColumnContentWidth($columnCount);
            list($width, $height) = ImageHelper::fitInBoundingBox($headerImage, (int) $maxImageWidth, (int) $maxImageWidth);
            $this->pdfDocument->Image($headerImage, $this->pdfSizes->getContentXStart(), $startY, $width, $height);
            $maxContentHeight = max($this->pdfDocument->GetY() + $height, $maxContentHeight);

            // set position for the next content
            ++$currentColumn;
        } else {
            --$columnCount;
        }

        $columnWidth = $this->pdfSizes->getColumnContentWidth($columnCount);

        // construction site description
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
        $this->pdfDocument->SetY($startY);

        $this->printH2($name, $columnWidth);
        $this->printP($address, $columnWidth);

        $this->pdfDocument->Ln($this->pdfSizes->getLnHeight() * M_PI);
        $this->printP($elements, $columnWidth, true);
        $maxContentHeight = max($this->pdfDocument->GetY(), $maxContentHeight);
        ++$currentColumn;

        // filter used for generation
        if ([] !== $filterEntries) {
            $this->pdfDocument->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
            $this->pdfDocument->SetY($startY);

            $this->printH2($filterHeader, $columnWidth);

            $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
            foreach ($filterEntries as $name => $value) {
                $this->pdfDocument->SetX($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
                $this->printHtmlP('<b>'.$name.'</b>: '.$value);
            }
        }

        // define start of next part
        $this->pdfDocument->SetY(max($this->pdfDocument->GetY(), $maxContentHeight) + $this->pdfSizes->getContentSpacerBig());
    }

    public function save(string $targetFilePath): void
    {
        $this->pdfDocument->Output($targetFilePath, 'F');
    }

    public function stream(): string
    {
        return $this->pdfDocument->Output($dest = 'S');
    }

    public function addMap(string $name, ?string $context, ?string $mapImageFilePath): void
    {
        $this->pdfDocument->AddPage();

        $this->setDefaults();

        $printTitle = function () use ($name, $context): void {
            $this->pdfDocument->SetY($this->pdfDocument->GetY() + $this->pdfSizes->getContentSpacerBig());
            $this->printH2($name, 0, $context);
        };

        $headerHeight = $this->getHeightOf(function () use ($printTitle): void {
            $printTitle();
        });

        if (file_exists($mapImageFilePath)) {
            $imgBorder = $this->pdfSizes->getImageBorder();
            $doubleImgPadding = 2 * $imgBorder;

            $maxWidth = $this->pdfSizes->getContentXSize() - $doubleImgPadding;
            $maxHeight = $this->pdfSizes->getContentYSize() - $headerHeight - $doubleImgPadding;
            list($width, $height) = ImageHelper::fitInBoundingBox($mapImageFilePath, $maxWidth, $maxHeight);

            // print title
            $printTitle();

            // print image with surrounding box
            $startY = $this->pdfDocument->GetY();
            $this->pdfDocument->Cell($this->pdfSizes->getContentXSize(), $height + $doubleImgPadding, '', 0, 2, '', true);
            $this->pdfDocument->SetY($startY);
            $this->pdfDocument->Image($mapImageFilePath, $this->pdfSizes->getContentXStart() + (($this->pdfSizes->getContentXSize() - $width) / 2), $this->pdfDocument->GetY() + $imgBorder, $width, $height);

            // adapt Y with spacer for next
            $this->pdfDocument->SetY($startY + $height + $doubleImgPadding + $this->pdfSizes->getContentSpacerBig());
        } else {
            // only print title
            $this->printH2($name, 0, $context);
        }
    }

    /**
     * @param string[]   $columnWidths
     * @param string[]   $head
     * @param string[][] $body
     */
    public function addSizedTable(array $columnWidths, array $head, array $body): void
    {
        $this->setDefaults();

        // adapt font for table content
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getSmallFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getEmphasisFontFamily());

        // make header upper case
        $row = [];
        foreach ($head as $item) {
            $row[] = mb_strtoupper($item, 'UTF-8');
        }

        // print header
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLightBackground());
        $this->printSizedRow($columnWidths, $row, true);

        // print content
        $currentRow = 0;
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLighterBackground());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        foreach ($body as $row) {
            $this->printSizedRow($columnWidths, $row, 1 === $currentRow % 2);
            ++$currentRow;
        }

        // define start of next part
        $this->pdfDocument->SetY($this->pdfDocument->GetY() + $this->pdfSizes->getContentSpacerBig());
    }

    /**
     * @param string|null $tableTitle
     * @param mixed|null  $firstColumnSize
     */
    public function addTable($tableHead, $tableContent, $tableFooter, $tableTitle = null, $firstColumnSize = null): void
    {
        $this->setDefaults();

        // print table header
        if (null !== $tableTitle) {
            $this->printH3($tableTitle, $this->pdfSizes->getContentXSize());
        }

        // adapt font for table content
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());

        $printEmphasizedRow = function ($content) use ($firstColumnSize): void {
            // make upper case
            $row = [];
            foreach ($content as $item) {
                $row[] = mb_strtoupper($item, 'UTF-8');
            }

            $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLightBackground());
            $this->pdfDocument->SetFont(...$this->pdfDesign->getEmphasisFontFamily());

            // print
            $maxTries = 3;
            while (!$this->printRow($row, true, $firstColumnSize) && $maxTries > 0) {
                // simply retry to print row if it did not work
                --$maxTries;
            }
        };

        $printEmphasizedRow($tableHead);

        // print content
        $currentRow = 0;
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLighterBackground());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        foreach ($tableContent as $row) {
            $maxTries = 3;
            while (!$this->printRow($row, 1 === $currentRow % 2, $firstColumnSize) && $maxTries > 0) {
                // simply retry to print row if it did not work
                --$maxTries;
            }
            ++$currentRow;
        }

        $printEmphasizedRow($tableFooter);

        // define start of next part
        $this->pdfDocument->SetY($this->pdfDocument->GetY() + $this->pdfSizes->getContentSpacerBig());
    }

    /**
     * @param array $imageGrid each grid entry must define an imagePath & identification
     */
    public function addImageGrid(array $imageGrid, int $columnCount): void
    {
        $this->setDefaults();

        $columnWidth = $this->pdfSizes->getColumnContentWidth($columnCount);

        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $cellWidthPadding = $this->pdfSizes->getTableCellPadding()[0] + $this->pdfSizes->getTableCellPadding()[2];
        foreach ($imageGrid as $row) {
            // get row height & calculate the other sizes
            $rowHeight = 0;
            foreach ($row as &$entry) {
                $imagePath = $entry['imagePath'];

                list($width, $height) = ImageHelper::fitInBoundingBox($imagePath, (int) $columnWidth, (int) $columnWidth);
                $rowHeight = max($rowHeight, $height);
                $entry['width'] = $width;
                $entry['height'] = $height;
            }

            // check if image fits on current page
            if ($this->pdfDocument->GetY() + $rowHeight + $this->pdfSizes->getColumnGutter() > $this->pdfSizes->getContentYEnd()) {
                // force new page
                $this->pdfDocument->AddPage();
                $this->pdfDocument->SetY($this->pdfSizes->getContentYStart());
            }
            $startY = $this->pdfDocument->GetY();

            // print images
            $currentColumn = 0;
            foreach ($row as &$entry) {
                // image
                $height = $entry['height'];
                $width = $entry['width'];
                $xStart = $this->pdfSizes->getColumnStart($currentColumn, $columnCount);
                $this->pdfDocument->Image($entry['imagePath'], $xStart, $startY, $width, $height, '', '', '', '', 300, '', false, false, 1);

                // identification
                $this->pdfDocument->SetXY($xStart, $startY);
                $width = mb_strlen((string) $entry['identification']) * $this->pdfSizes->getRegularFontSize() / 5 + $cellWidthPadding;
                $this->pdfDocument->Cell($width, 0, $entry['identification'], 0, 0, '', true);
                ++$currentColumn;
            }

            $this->pdfDocument->SetY($startY + $rowHeight + $this->pdfSizes->getColumnGutter());
        }
    }

    private function setDefaults(): void
    {
        // set typography
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->SetLineWidth($this->pdfSizes->getLineWidth());

        // set colors
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLightBackground());
        $this->pdfDocument->SetDrawColor(...$this->pdfDesign->getDarkBackground());
        $this->pdfDocument->SetTextColor(...$this->pdfDesign->getTextColor());

        // set layout
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getContentXStart());
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getDefaultCellPadding());

        // set position
        $this->pdfDocument->SetX($this->pdfSizes->getContentXStart());
        if ($this->pdfDocument->GetY() > $this->pdfSizes->getContentYEnd()) {
            $this->pdfDocument->AddPage();
            $this->pdfDocument->SetY($this->pdfSizes->getContentYStart());
        }
    }

    private function printH2(string $text, float|int $columnWidth = 0, ?string $description = ''): void
    {
        if (mb_strlen($description) > 0) {
            $this->pdfDocument->SetTextColor(...$this->pdfDesign->getSecondaryTextColor());
            $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
            $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
            $this->pdfDocument->MultiCell($columnWidth, 0, $description, 0, 'L', false, 1);
        }

        $this->pdfDocument->SetTextColor(...$this->pdfDesign->getTextColor());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getBigFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 1);

        $this->pdfDocument->Ln($this->pdfSizes->getLnHeight());
    }

    private function printH3($text, float $columnWidth = 0, $description = ''): void
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

    private function printP(string $text, float $columnWidth = 0, bool $secondary = false): void
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

    private function printHtmlP(string $html): void
    {
        $this->pdfDocument->writeHTMLCell(0, 0, $this->pdfDocument->GetX(), $this->pdfDocument->GetY(), $html, 0, 1);
        // -2 because the html does not stop at the correct height
        $this->pdfDocument->SetY($this->pdfDocument->GetY());
    }

    private function getHeightOf(\Closure $closure): int|float
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
     * if return false, call again with same parameters.
     *
     * @param float[]  $columnWidths
     * @param string[] $row
     */
    private function printSizedRow(array $columnWidths, array $row, bool $fill = false, bool $retry = false): void
    {
        // put columns
        $currentContentHeight = 0;
        $startY = $this->pdfDocument->GetY();
        $startPage = $this->pdfDocument->getPage();
        $this->pdfDocument->startTransaction();

        $currentXStart = $this->pdfSizes->getContentXStart();
        $counter = count($columnWidths);
        for ($i = 0; $i < $counter; ++$i) {
            $currentColumn = $row[$i];
            $currentWidth = $columnWidths[$i];

            // draw column content
            $this->pdfDocument->SetXY($currentXStart, $startY);
            $this->pdfDocument->MultiCell($currentWidth, $currentContentHeight, $currentColumn, 0, 'L', $fill, 1);

            // if new page started; remove from old page and retry on new page
            if ($this->pdfDocument->getPage() > $startPage) {
                $this->pdfDocument->rollbackTransaction(true);
                if ($retry) {
                    // tried twice; content does not fit on a single page. We abort, as we do not deal with this edge case
                    return;
                }

                $this->pdfDocument->AddPage();

                // add top border of table
                $lineX = $this->pdfSizes->getContentYStart();
                $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $lineX, $this->pdfSizes->getContentXEnd(), $lineX);

                // set start position
                $this->pdfDocument->SetXY($this->pdfSizes->getContentXStart(), $lineX + $this->pdfSizes->getLineWidth());

                // retry
                $this->printSizedRow($columnWidths, $row, $fill, true);

                return;
            }

            // if row is higher now than before; draw background from preceding columns
            $actualContentHeight = $this->pdfDocument->GetY() - $startY;
            if ($actualContentHeight > $currentContentHeight) {
                // redraw fill of previous cells
                if ($fill && $i > 0) {
                    $this->pdfDocument->SetXY($this->pdfSizes->getContentXStart(), $startY + $currentContentHeight);
                    $this->pdfDocument->SetCellPadding(0);
                    $this->pdfDocument->Cell($currentXStart - $this->pdfSizes->getContentXStart(), $actualContentHeight - $currentContentHeight, '', 0, 0, '', $fill);
                    $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
                }

                $currentContentHeight = $actualContentHeight;
            }

            $currentXStart += $currentWidth;
        }

        // draw finishing line & set position for new row
        $contentEnd = $startY + $currentContentHeight;
        $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $contentEnd, $this->pdfSizes->getContentXEnd(), $contentEnd);
        $this->pdfDocument->SetY($contentEnd + $this->pdfSizes->getLineWidth());

        $this->pdfDocument->commitTransaction();
    }

    private function printRow($row, bool $fill, $firstColumnSize = null): bool
    {
        // alternative background colors
        $columnCount = \count($row);

        // put columns
        $maxContentHeight = 0;
        $currentColumn = 0;
        $fullWidth = 0;
        $startY = $this->pdfDocument->GetY();
        $startPage = $this->pdfDocument->getPage();
        $this->pdfDocument->startTransaction();
        foreach ($row as $column) {
            $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount, $firstColumnSize), $startY);
            $currentWidth = $this->pdfSizes->getColumnWidth($currentColumn, $columnCount, $firstColumnSize);

            // draw cell content
            $this->pdfDocument->MultiCell($currentWidth, $maxContentHeight - $startY, $column, 0, 'L', $fill, 1);

            // if new page started; remove from old page and retry on new page
            if ($this->pdfDocument->getPage() > $startPage) {
                $this->pdfDocument->rollbackTransaction(true);

                $this->pdfDocument->AddPage();

                $lineX = $this->pdfSizes->getContentYStart();
                $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $lineX, $this->pdfSizes->getContentXEnd(), $lineX);

                $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart(0, $columnCount) + $this->pdfSizes->getLineWidth(), $lineX);

                return false;
            }

            // if row is higher now than before; draw background from preceding columns
            if (0 !== $maxContentHeight && $this->pdfDocument->GetY() > $maxContentHeight) {
                $diff = $this->pdfDocument->GetY() - $maxContentHeight;
                $newMaxHeight = $this->pdfDocument->GetY();

                // redraw fill if needed
                if ($fill) {
                    $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $maxContentHeight, $firstColumnSize);
                    $this->pdfDocument->SetCellPadding(0);
                    $this->pdfDocument->Cell($fullWidth, $diff, '', 0, 0, '', $fill);
                    $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
                }

                // set position for new row
                $this->pdfDocument->SetY($newMaxHeight);
                $maxContentHeight += $diff;
            } else {
                $maxContentHeight = $this->pdfDocument->GetY();
            }
            ++$currentColumn;
            $fullWidth += $currentWidth;
        }

        // draw finishing line & set position for new row
        $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $maxContentHeight, $this->pdfSizes->getContentXEnd(), $maxContentHeight);
        $this->pdfDocument->SetY($maxContentHeight + $this->pdfSizes->getLineWidth());

        $this->pdfDocument->commitTransaction();

        return true;
    }

    public function getContentWidth(): float
    {
        return $this->pdfSizes->getContentXSize();
    }
}
