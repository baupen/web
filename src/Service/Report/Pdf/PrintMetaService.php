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
use App\Service\Report\Document\Interfaces\PrintMetaServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;

class PrintMetaService implements PrintMetaServiceInterface
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
     * @param Pdf $pdf
     */
    public function initializeLayout(Pdf $pdf)
    {
        //set margin
        $pdf->SetMargins($this->layoutService->getContentXStart(), $this->layoutService->getContentYStart());
        $pdf->SetAutoPageBreak(true, $this->layoutService->getMarginBottom());
    }

    /**
     * @param Pdf $pdf
     * @param string $headerLeft
     */
    public function printHeaderLeft(Pdf $pdf, string $headerLeft)
    {
        $pdf->SetXY($this->layoutService->getContentXStart(), $this->layoutService->getHeaderYStart());

        $pdf->SetFontSize($this->typographyService->getHeaderFontSize());
        $maxWidth = $this->layoutService->getContentXSize() / 3 * 2;
        $pdf->Cell($maxWidth, 0, $headerLeft, 0, 0, 'L');
    }

    /**
     * @param Pdf $pdf
     * @param string $logoPath
     */
    public function printLogo(Pdf $pdf, string $logoPath)
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
     * @param Pdf $pdf
     * @param string $footerLeft
     */
    public function printFooterLeft(Pdf $pdf, string $footerLeft)
    {
        $pdf->SetXY($this->layoutService->getContentXStart(), $this->layoutService->getFooterYStart());

        $pdf->SetFontSize($this->typographyService->getFooterFontSize());
        $pdf->Cell($this->layoutService->getContentXSize(), 0, $footerLeft, 0, 0, 'L');
    }

    /**
     * @param Pdf $pdf
     */
    public function printPageNumbers(Pdf $pdf)
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
