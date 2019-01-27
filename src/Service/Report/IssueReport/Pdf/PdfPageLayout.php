<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Pdf;

use App\Helper\ImageHelper;
use App\Service\Report\Document\Pdf\Cursor;
use App\Service\Report\Document\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Document\Pdf\Interfaces\PdfPageLayoutInterface;
use App\Service\Report\IssueReport\Model\MetaData;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\TypographyServiceInterface;

class PdfPageLayout implements PdfPageLayoutInterface
{
    /**
     * @var LayoutServiceInterface
     */
    private $layout;

    /**
     * @var TypographyServiceInterface
     */
    private $typography;

    /**
     * @var MetaData
     */
    private $content;

    /**
     * Printer constructor.
     *
     * @param LayoutServiceInterface $layoutService
     * @param TypographyServiceInterface $typographyService
     * @param MetaData $content
     */
    public function __construct(LayoutServiceInterface $layoutService, TypographyServiceInterface $typographyService, MetaData $content)
    {
        $this->layout = $layoutService;
        $this->typography = $typographyService;
        $this->content = $content;
    }

    /**
     * @param PdfDocumentInterface $pdf
     */
    public function initializeLayout(PdfDocumentInterface $pdf)
    {
        $marginLeft = $this->layout->getContentXStart();
        $marginTop = $this->layout->getContentYStart();
        $marginRight = $this->layout->getMarginRight();
        $marginBottom = $this->layout->getMarginBottom();
        $pdf->setPageMargins($marginLeft, $marginTop, $marginRight, $marginBottom);

        if ($pdf->getPdfImplementation() === PdfDocumentInterface::PDF_IMPLEMENTATION_TCPDF) {
            $pdf->startNewPage();
        }

        $pdf->setMeta($this->content->getTitle(), $this->content->getAuthor());
    }

    /**
     * @param PdfDocumentInterface $pdf
     */
    public function printHeader(PdfDocumentInterface $pdf)
    {
        $this->printHeaderLeft($pdf, $this->content->getTitle());
        $this->printLogo($pdf, $this->content->getLogoPath());
    }

    /**
     * @param PdfDocumentInterface $pdf
     * @param int $currentPage
     * @param int $totalPages
     */
    public function printFooter(PdfDocumentInterface $pdf, int $currentPage, int $totalPages)
    {
        $this->printFooterLeft($pdf, $this->content->getGenerationInfoText());
        $this->printPageNumbers($pdf, $currentPage, $totalPages);
    }

    /**
     * @param PdfDocumentInterface $pdf
     * @param string $headerLeft
     */
    private function printHeaderLeft(PdfDocumentInterface $pdf, string $headerLeft)
    {
        $maxWidth = $this->layout->getContentXSize() / 3 * 2;

        $pdf->setCursor(new Cursor($this->layout->getContentXStart(), $this->layout->getHeaderYStart()));
        $pdf->configure(['fontSize' => $this->typography->getHeaderFontSize()]);
        $pdf->printText($headerLeft, $maxWidth);
    }

    /**
     * @param PdfDocumentInterface $pdf
     * @param string $logoPath
     */
    private function printLogo(PdfDocumentInterface $pdf, string $logoPath)
    {
        // calculate optimal size
        $maxHeight = $this->layout->getHeaderHeight();
        $maxWidth = $this->layout->getContentXSize() / 3;
        list($width, $height) = ImageHelper::getWidthHeightArguments($logoPath, $maxWidth, $maxHeight);

        // print
        $startX = $this->layout->getContentXEnd() - $width;
        $startY = $this->layout->getHeaderYStart();
        $pdf->setCursor(new Cursor($startX, $startY));
        $pdf->printImage($logoPath, $width, $height);
    }

    /**
     * @param PdfDocumentInterface $pdf
     * @param string $footerLeft
     */
    private function printFooterLeft(PdfDocumentInterface $pdf, string $footerLeft)
    {
        $pdf->setCursor(new Cursor($this->layout->getContentXStart(), $this->layout->getFooterYStart()));
        $pdf->printText($footerLeft, $this->typography->getFooterFontSize());
    }

    /**
     * @param PdfDocumentInterface $pdf
     * @param int $currentPageNumber
     * @param int $totalPageNumbers
     */
    private function printPageNumbers(PdfDocumentInterface $pdf, int $currentPageNumber, int $totalPageNumbers)
    {
        $contentWidthPart = $this->layout->getContentXSize() / 8;
        $startX = $this->layout->getContentXEnd() - $contentWidthPart;

        if ($pdf->getPdfImplementation() === PdfDocumentInterface::PDF_IMPLEMENTATION_TCPDF) {
            // TCPDF uses a placeholder for the page numbers which is replaced at the end. this leads to incorrect alignment.
            $startX += 6.5;
        }

        $startY = $this->layout->getFooterYStart();

        $pdf->setCursor(new Cursor($startX, $startY));
        $pdf->configure(['fontSize' => $this->typography->getFooterFontSize(), 'alignment' => 'R']);
        $pdf->printText($currentPageNumber . '/' . $totalPageNumbers, $contentWidthPart);
    }
}
