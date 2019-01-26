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
    private $configuration;

    /**
     * @var bool
     */
    private $configurationChanged = true;

    /**
     * @var float
     */
    private $marginBottom = 0;

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
        $this->configuration = new PrintConfiguration();
    }

    /**x
     * @param Cursor $cursor
     */
    public function setCursor(Cursor $cursor)
    {
        $this->pdf->SetXY($cursor->getXCoordinate(), $cursor->getYCoordinate());
        if ($cursor->getPage() !== null) {
            $this->pdf->setPage($cursor->getPage());
        }
    }

    /**
     * @param string $text
     * @param float $width
     */
    public function printText(string $text, float $width)
    {
        $this->ensurePrintConfigurationApplied();

        $align = $this->configuration->getAlignment();
        $fill = $this->configuration->getFill();
        $border = $this->configuration->getBorder();

        $this->pdf->MultiCell($width, 0, $text, $border, $align, $fill);
    }

    /**
     * applies the config if it has changed.
     */
    private function ensurePrintConfigurationApplied()
    {
        if (!$this->configurationChanged) {
            return;
        }

        $this->configuration->apply($this->pdf);
        $this->configurationChanged = false;
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

        $this->marginBottom = $marginBottom;
    }

    /**
     * @param string $imagePath
     * @param float $width
     * @param float $height
     */
    public function printImage(string $imagePath, float $width, float $height)
    {
        $this->ensurePrintConfigurationApplied();

        $align = $this->configuration->getAlignment();

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
    public function configure(array $config = [], bool $restoreDefaults = true)
    {
        $this->configurationChanged = true;

        if ($restoreDefaults) {
            $this->configuration = new PrintConfiguration();
        }

        $this->configuration->setConfiguration($config);
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
     * @return PrintConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param PrintConfiguration $printConfiguration
     */
    public function setConfiguration(PrintConfiguration $printConfiguration)
    {
        $this->configuration = $printConfiguration;
        $this->configurationChanged = true;
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

    /**
     * @param Cursor $target
     *
     * @throws \Exception
     */
    public function drawUntil(Cursor $target)
    {
        $start = $this->getCursor();
        $current = $start;

        // switch cursors if other order than expected
        if ($current->isLowerOnPageThan($target)) {
            $current = $target;
            $target = $start;
        }

        // draws between the two specified cursors
        $drawBetween = function (Cursor $source, Cursor $target) {
            $this->setCursor($source);
            $width = $target->getXCoordinate() - $source->getXCoordinate();
            $height = $target->getYCoordinate() - $source->getYCoordinate();
            $this->pdf->Cell($width, $height, '', $this->configuration->getBorder(), 0, '', $this->configuration->getFill());
        };

        $this->ensurePrintConfigurationApplied();

        // draws until the target cursor is reached while respecting page boundaries
        while ($current->getPage() !== $target->getPage()) {
            $until = new Cursor($target->getXCoordinate(), $this->pdf->getMaxContentHeight(), $current->getPage());
            $drawBetween($current, $until);

            $current = new Cursor($current->getXCoordinate(), $this->pdf->getContentStart(), $current->getPage() + 1);
        }

        // pages match;
        $drawBetween($current, $target);

        // reset cursor
        $this->setCursor($start);
    }
}
