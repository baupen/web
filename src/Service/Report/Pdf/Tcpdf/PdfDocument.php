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

use App\Service\Report\Pdf\Cursor;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Tcpdf\Configuration\PrintConfiguration;

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
     * @var PrintConfiguration
     */
    private $printConfiguration;

    /**
     * @var bool
     */
    private $printConfigurationHasChanged = true;

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
        $this->printConfiguration = new PrintConfiguration();
    }

    /**x
     * @param Cursor $cursor
     */
    public function setCursor(Cursor $cursor)
    {
        $this->pdf->SetXY($cursor->getXCoordinate(), $cursor->getYCoordinate());
        $this->pdf->setPage($cursor->getPage());
    }

    /**
     * @param string $text
     * @param float $width
     */
    public function printText(string $text, float $width)
    {
        $this->ensurePrintConfigurationApplied();

        $align = $this->printConfiguration->getAlignment();
        $fill = $this->printConfiguration->getFill();
        $border = $this->printConfiguration->getBorder();

        $this->pdf->MultiCell($width, 0, $text, $border, $align, $fill);
    }

    /**
     * applies the config if it has changed.
     */
    private function ensurePrintConfigurationApplied()
    {
        if ($this->printConfigurationHasChanged) {
            $this->printConfiguration->apply($this->pdf);
            $this->printConfigurationHasChanged = false;
        }
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
        $this->ensurePrintConfigurationApplied();

        $align = $this->printConfiguration->getAlignment();

        $this->pdf->Image($imagePath, '', '', $width, $height, '', '', $align);
    }

    /**
     * @param \Closure $printClosure
     *
     * @return bool
     */
    public function causesPageBreak(\Closure $printClosure)
    {
        list($cursorBefore, $cursorAfter) = $this->measureImpact($printClosure);

        return $cursorBefore->getPage() < $cursorAfter->getPage();
    }

    /**
     * returns the active cursor position.
     *
     * @return Cursor
     */
    public function getCursor()
    {
        return new Cursor($this->pdf->GetX(), $this->pdf->GetY(), $this->pdf->PageNo());
    }

    /**
     * starts a new page and puts cursor on it.
     */
    public function startNewPage()
    {
        $this->pdf->AddPage();
    }

    /**
     * @param array $config
     * @param bool $restoreDefaults
     *
     * @throws \Exception
     */
    public function configurePrint(array $config = [], bool $restoreDefaults = true)
    {
        $this->printConfigurationHasChanged = true;

        if ($restoreDefaults) {
            $this->printConfiguration = new PrintConfiguration();
        }

        $this->printConfiguration->setConfiguration($config);
    }

    /**
     * @param string $text
     *
     * @return float
     */
    public function calculateWidthOfText(string $text)
    {
        $pdf = $this->pdf;
        list($cursorBefore, $cursorAfter) = $this->measureImpact(function () use ($text, $pdf) {
            $pdf->MultiCell(0, 0, $text, 0, 'L', false, 0);
        });

        return $cursorAfter->getYCoordinate() - $cursorBefore->getYCoordinate();
    }

    /**
     * @param \Closure $printClosure
     *
     * @return Cursor[]
     */
    private function measureImpact(\Closure $printClosure)
    {
        $this->pdf->startTransaction();
        $this->pdf->checkPageBreak();
        $cursorBefore = $this->getCursor();

        $printClosure();

        $cursorAfter = $this->getCursor();
        $this->pdf->rollbackTransaction(true);

        return [$cursorBefore, $cursorAfter];
    }

    /**
     * @return object
     */
    public function getPrintConfiguration()
    {
        return $this->printConfiguration;
    }

    /**
     * @param $printConfiguration
     */
    public function setPrintConfiguration($printConfiguration)
    {
        $this->printConfiguration = $printConfiguration;
        $this->printConfigurationHasChanged = true;
    }

    /**
     * @param \Closure $printClosure
     *
     * @return Cursor
     */
    public function cursorAfterwardsIfPrinted(\Closure $printClosure)
    {
        [, $after] = $this->measureImpact($printClosure);

        return $after;
    }
}
