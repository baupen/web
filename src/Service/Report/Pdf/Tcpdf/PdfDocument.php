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
