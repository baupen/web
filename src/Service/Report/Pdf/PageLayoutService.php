<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf;

use App\Helper\ImageHelper;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Interfaces\PageLayoutServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class PageLayoutService implements PageLayoutServiceInterface
{
    /**
     * @var LayoutServiceInterface
     */
    private $layoutService;

    /**
     * @var TypographyServiceInterface
     */
    private $typographyService;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * HeaderFooterServiceInterface constructor.
     *
     * @param LayoutServiceInterface $pdfSizes
     * @param TypographyServiceInterface $typographyService
     * @param PathServiceInterface $pathService
     */
    public function __construct(LayoutServiceInterface $pdfSizes, TypographyServiceInterface $typographyService, PathServiceInterface $pathService)
    {
        $this->layoutService = $pdfSizes;
        $this->typographyService = $typographyService;
        $this->pathService = $pathService;
    }

    /**
     * @param PdfDocumentInterface $pdf
     */
    public function initializeLayout(PdfDocumentInterface $pdf)
    {
        $marginLeft = $this->layoutService->getContentXStart();
        $marginTop = $this->layoutService->getContentYStart();
        $marginRight = $this->layoutService->getMarginRight();
        $marginBottom = $this->layoutService->getMarginBottom();
        $pdf->setPageMargins($marginLeft, $marginTop, $marginRight, $marginBottom);
        $pdf->startNewPage();
    }

    /**
     * @param PdfDocumentInterface $pdf
     * @param string $headerLeft
     */
    public function printHeaderLeft(PdfDocumentInterface $pdf, string $headerLeft)
    {
        $maxWidth = $this->layoutService->getContentXSize() / 3 * 2;

        $pdf->setCursor(new Cursor($this->layoutService->getContentXStart(), $this->layoutService->getHeaderYStart()));
        $pdf->configure(['fontSize' => $this->typographyService->getHeaderFontSize()]);
        $pdf->printText($headerLeft, $maxWidth);
    }

    /**
     * @param PdfDocumentInterface $pdf
     * @param string $logoPath
     */
    public function printLogo(PdfDocumentInterface $pdf, string $logoPath)
    {
        // calculate optimal size
        $maxHeight = $this->layoutService->getHeaderHeight();
        $maxWidth = $this->layoutService->getContentXSize() / 3;
        list($width, $height) = ImageHelper::getWidthHeightArguments($logoPath, $maxWidth, $maxHeight);

        // print
        $startX = $this->layoutService->getContentXEnd() - $width;
        $startY = $this->layoutService->getHeaderYStart();
        $pdf->setCursor(new Cursor($startX, $startY));
        $pdf->printImage($logoPath, $width, $height);
    }

    /**
     * @param PdfDocumentInterface $pdf
     * @param string $footerLeft
     */
    public function printFooterLeft(PdfDocumentInterface $pdf, string $footerLeft)
    {
        $pdf->setCursor(new Cursor($this->layoutService->getContentXStart(), $this->layoutService->getFooterYStart()));
        $pdf->printText($footerLeft, $this->typographyService->getFooterFontSize());
    }

    /**
     * @param PdfDocumentInterface $pdf
     * @param int $currentPageNumber
     * @param int $totalPageNumbers
     */
    public function printPageNumbers(PdfDocumentInterface $pdf, int $currentPageNumber, int $totalPageNumbers)
    {
        $contentWidthPart = $this->layoutService->getContentXSize() / 8;
        //+6.5 because TCPDF uses a placeholder for the page numbers which is replaced at the end. this leads to incorrect alignment.
        $startX = $this->layoutService->getContentXEnd() - $contentWidthPart + 6.5;
        $startY = $this->layoutService->getFooterYStart();

        $pdf->setCursor(new Cursor($startX, $startY));
        $pdf->configure(['fontSize' => $this->typographyService->getFooterFontSize(), 'alignment' => 'R']);
        $pdf->printText($currentPageNumber . '/' . $totalPageNumbers, $contentWidthPart);
    }
}
