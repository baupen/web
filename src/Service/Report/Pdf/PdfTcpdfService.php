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
use App\Service\Report\CleanPdf;
use App\Service\Report\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Interfaces\TcpdfServiceInterface;
use App\Service\Report\PdfSizes;

class PdfTcpdfService implements TcpdfServiceInterface
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
     * HeaderFooterServiceInterface constructor.
     *
     * @param LayoutServiceInterface $pdfSizes
     * @param TypographyServiceInterface $typographyService
     */
    public function __construct(LayoutServiceInterface $pdfSizes, TypographyServiceInterface $typographyService)
    {
        $this->layoutService = $pdfSizes;
        $this->typographyService = $typographyService;
    }

    /**
     * @param CleanPdf $pdf
     */
    public function initialize(CleanPdf $pdf)
    {
        $pdf->SetMargins($this->layoutService->getContentXStart(), $this->layoutService->getContentYStart());
        $pdf->SetAutoPageBreak(true, $this->layoutService->getMarginBottom());
    }

    /**
     * @param CleanPdf $cleanPdf
     * @param string $title
     * @param string $author
     */
    public function setMeta(CleanPdf $cleanPdf, string $title, string $author)
    {
        $cleanPdf->SetCreator(PDF_CREATOR);
        $cleanPdf->SetAuthor($author);
        $cleanPdf->SetTitle($title);
    }

    /**
     * @param CleanPdf $pdf
     * @param string $title
     * @param string $logoPath
     */
    public function printHeader(CleanPdf $pdf, string $title, string $logoPath)
    {
        $this->printTitle($pdf, $title);
        $this->printLogo($pdf, $logoPath);
    }

    /**
     * @param CleanPdf $pdf
     * @param string $author
     */
    public function printFooter(CleanPdf $pdf, string $author)
    {
        $this->printAuthor($pdf, $author);
        $this->printPageNumbers($pdf);
    }

    /**
     * @param CleanPdf $pdf
     * @param string $title
     */
    private function printTitle(CleanPdf $pdf, string $title)
    {
        $pdf->SetXY($this->layoutService->getContentXStart(), $this->layoutService->getHeaderYStart());

        $pdf->SetFontSize($this->typographyService->getHeaderFontSize());
        $maxWidth = $this->layoutService->getContentXSize() / 3 * 2;
        $pdf->Cell($maxWidth, 0, $title, 0, 0, 'L');
    }

    /**
     * @param CleanPdf $pdf
     * @param string $logoPath
     */
    private function printLogo(CleanPdf $pdf, string $logoPath)
    {
        // calculate optimal size
        $maxHeight = $this->layoutService->getHeaderHeight();
        $maxWidth = $this->layoutService->getContentXSize() / 3;
        list($width, $height) = ImageHelper::getWidthHeightArguments($logoPath, $maxWidth, $maxHeight);

        // print
        $startX = $this->layoutService->getContentXEnd() - $width;
        $startY = $this->layoutService->getHeaderYStart();
        $pdf->Image($logoPath, $startX, $startY, $width, $height, '', '', 'R');
    }

    /**
     * @param CleanPdf $pdf
     * @param string $author
     */
    private function printAuthor(CleanPdf $pdf, string $author)
    {
        $pdf->SetXY($this->layoutService->getContentXStart(), $this->layoutService->getFooterYStart());

        $pdf->SetFontSize($this->typographyService->getFooterFontSize());
        $pdf->Cell($this->layoutService->getContentXSize(), 0, $author, 0, 0, 'L');
    }

    /**
     * @param CleanPdf $pdf
     */
    private function printPageNumbers(CleanPdf $pdf)
    {
        $contentWidthPart = $this->layoutService->getContentXSize() / 8;
        //+6.5 because TCPDF uses a placeholder for the page numbers which is replaced at the end. this leads to incorrect alignment.
        $pdf->SetXY($this->layoutService->getContentXEnd() - $contentWidthPart + 6.5, $this->layoutService->getFooterYStart());

        $currentPage = $pdf->getAliasNumPage();
        $totalPages = $pdf->getAliasNbPages();
        $pdf->SetFontSize($this->typographyService->getFooterFontSize());
        $pdf->Cell($contentWidthPart, 0, $currentPage . '/' . $totalPages, 0, 0, 'R');
    }
}
