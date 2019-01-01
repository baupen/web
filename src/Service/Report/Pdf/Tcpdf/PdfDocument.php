<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Tcpdf;

use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

/**
 * implements the predictable publish PdfDocumentInterface with the TCPDF library.
 *
 * Class PdfDocument
 */
class PdfDocument implements PdfDocumentInterface
{
    /**
     * @var Pdf
     */
    private $pdf;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var float
     */
    private $defaultWidth;

    /**
     * PdfDocument constructor.
     *
     * @param Pdf $pdf
     */
    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
        $this->pdf->SetCreator(PDF_CREATOR);

        $this->identifier = uniqid();
    }

    /**
     * @param float $xCoordinate
     * @param float $yCoordinate
     */
    public function setCursor(float $xCoordinate, float $yCoordinate)
    {
        $this->pdf->SetXY($xCoordinate, $yCoordinate);
    }

    /**
     * @param string $text
     * @param float $textSize
     * @param float $width
     * @param bool $alignRight
     */
    public function printText(string $text, float $textSize, float $width = null, bool $alignRight = false)
    {
        if ($width === null) {
            $width = $this->defaultWidth;
        }

        $this->pdf->SetFontSize($textSize);
        $this->pdf->MultiCell($width, 0, $text, 0, $alignRight ? 'R' : 'L');
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $title
     * @param string $author
     */
    public function setMeta(string $title, string $author)
    {
        $this->pdf->SetTitle($title);
        $this->pdf->SetAuthor($author);
    }

    /**
     * @param string $filePath
     */
    public function save(string $filePath)
    {
        $this->pdf->Output($filePath, 'F');
    }

    /**
     * @param float $marginLeft
     * @param float $marginTop
     * @param float $marginRight
     * @param float $marginBottom
     */
    public function setPageMargins(float $marginLeft, float $marginTop, float $marginRight, float $marginBottom)
    {
        $this->pdf->SetMargins($marginLeft, $marginTop, $marginRight);
        $this->pdf->SetAutoPageBreak(true, $marginBottom);
    }

    /**
     * @param string $imagePath
     * @param float $width
     * @param float $height
     */
    public function printImage(string $imagePath, float $width, float $height)
    {
        $this->pdf->Image($imagePath, '', '', $width, $height, '', '', 'R');
    }

    /**
     * @param int $page
     */
    public function setPage(int $page)
    {
        $this->pdf->setPage($page);
    }

    /**
     * @param string $text
     * @param float $textSize
     * @param float $width
     * @param bool $alignRight
     */
    public function printBoldText(string $text, float $textSize, float $width = null, bool $alignRight = false)
    {
        // TODO: Implement printBoldText() method.
    }

    /**
     * @param string[] $header
     * @param string[] $content
     * @param float|null $width
     */
    public function printTable(array $header, array $content, float $width = null)
    {
        // TODO: Implement printTable() method.
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
        $this->pdf->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $this->pdf->SetFontSize($this->pdfSizes->getTextFontSize());
        $this->pdf->SetFont(...$this->pdfDesign->getEmphasisFontFamily());

        //make header upper case
        $row = [];
        foreach ($tableHead as $item) {
            $row[] = mb_strtoupper($item, 'UTF-8');
        }

        //print header
        $this->pdf->SetFillColor(...$this->pdfDesign->getLightBackground());
        $maxTries = 3;
        while (!$this->printRow($row, true, $this->pdfDesign->getLightBackground()) && $maxTries > 0) {
            //simply retry to print row if it did not work
            --$maxTries;
        }

        //print content
        $currentRow = 0;
        $this->pdf->SetFillColor(...$this->pdfDesign->getLighterBackground());
        $this->pdf->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        foreach ($tableContent as $row) {
            $maxTries = 3;
            while (!$this->printRow($row, $currentRow % 2 === 1, $this->pdfDesign->getLighterBackground()) && $maxTries > 0) {
                //simply retry to print row if it did not work
                --$maxTries;
            }
            ++$currentRow;
        }

        //define start of next part
        $this->pdf->SetY($this->pdf->GetY() + $this->pdfSizes->getContentSpacerBig());
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
        $startY = $this->pdf->GetY();
        $startPage = $this->pdf->getPage();
        foreach ($row as $item) {
            $this->pdf->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $startY);
            $currentWidth = $this->pdfSizes->getColumnWidth($currentColumn, $columnCount);

            //draw cell content
            $this->pdf->MultiCell($currentWidth, $maxContentHeight - $startY, $item, 0, 'L', $fill, 1);

            //if new page started; remove from old page and retry on new page
            if ($this->pdf->getPage() > $startPage) {
                $newHeight = $this->pdf->GetY();

                //row did not fit on current page; start over on new page
                //print over started row
                $this->pdf->setPage($startPage);
                $this->pdf->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $startY);
                $this->pdf->SetCellPadding(0);
                $this->pdf->SetFillColor(...$this->pdfDesign->getWhiteBackground());
                $this->pdf->Cell($this->pdfSizes->getContentXSize(), $this->pdfSizes->getContentYEnd() - $startY, '', 0, 0, '', true);

                //go to new page
                $this->pdf->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $this->pdfSizes->getContentYStart());
                $this->pdf->setPage($startPage + 1);

                //print over started row
                if ($fill) {
                    $this->pdf->SetFillColor(...$fillBackground);
                }
                $this->pdf->Cell($this->pdfSizes->getContentXSize(), $newHeight - $this->pdfSizes->getContentYStart(), '', 0, 0, '', true);
                //draw line
                $lineX = $this->pdfSizes->getContentYStart();
                $this->pdf->Line($this->pdfSizes->getContentXStart(), $lineX, $this->pdfSizes->getContentXEnd(), $lineX);

                //set position to start new row
                $this->pdf->SetXY($this->pdfSizes->getColumnStart(0, $columnCount) + $this->pdfSizes->getLineWidth(), $lineX);

                //reset colors
                $this->pdf->SetFillColor(...$fillBackground);
                $this->pdf->setCellPaddings(...$this->pdfSizes->getTableCellPadding());

                return false;
            }

            //if row is higher now than before; draw background from preceding columns
            if ($maxContentHeight !== 0 && $this->pdf->GetY() > $maxContentHeight) {
                $diff = $this->pdf->GetY() - $maxContentHeight;
                $newMaxHeight = $this->pdf->GetY();

                //redraw fill if needed
                if ($fill) {
                    $this->pdf->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $maxContentHeight);
                    $this->pdf->SetCellPadding(0);
                    $this->pdf->Cell($fullWidth, $diff, '', 0, 0, '', $fill);
                    $this->pdf->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
                }

                //set position for new row
                $this->pdf->SetY($newMaxHeight);
                $maxContentHeight += $diff;
            } else {
                $maxContentHeight = $this->pdf->GetY();
            }
            ++$currentColumn;
            $fullWidth += $currentWidth;
        }

        //draw finishing line & set position for new row
        $this->pdf->Line($this->pdfSizes->getContentXStart(), $maxContentHeight, $this->pdfSizes->getContentXEnd(), $maxContentHeight);
        $this->pdf->SetY($maxContentHeight + $this->pdfSizes->getLineWidth());

        return true;
    }

    /**
     * @param \Closure $printClosure
     *
     * @return bool
     */
    public function provocatesPageBreak(\Closure $printClosure)
    {
        // remember current position
        $this->pdf->startTransaction();
        $this->pdf->checkPageBreak();
        $startPage = $this->pdf->getPage();

        // print
        $printClosure();

        // save position
        $endPage = $this->pdf->getPage();

        return $endPage > $startPage;
    }

    /**
     * returns the active cursor position as an array of [$xCoordinate, $yCoordinate].
     *
     * @return int[]
     */
    public function getCursor()
    {
        return [$this->pdf->GetX(), $this->pdf->GetY()];
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->pdf->PageNo();
    }

    public function startNewPage()
    {
        $this->pdf->AddPage();
    }
}
