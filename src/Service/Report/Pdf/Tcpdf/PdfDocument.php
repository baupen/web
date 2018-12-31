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
        $this->pdf->Cell($width, 0, $textSize, 0, 0, $alignRight ? 'R' : 'L');
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
}
