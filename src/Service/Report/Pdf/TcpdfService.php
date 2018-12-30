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

use App\Service\Interfaces\PathServiceInterface;
use App\Service\Report\Document\Interfaces\PrintMetaServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Interfaces\TcpdfServiceInterface;
use App\Service\Report\PdfSizes;

class TcpdfService implements TcpdfServiceInterface
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
     * @var PrintMetaServiceInterface
     */
    private $printMetaService;

    /**
     * @var string
     */
    private $headerLeft;

    /**
     * @var string
     */
    private $footerLeft;

    /**
     * @var string
     */
    private $logoPath;

    /**
     * HeaderFooterServiceInterface constructor.
     *
     * @param LayoutServiceInterface $pdfSizes
     * @param TypographyServiceInterface $typographyService
     * @param PathServiceInterface $pathService
     * @param PrintMetaServiceInterface $printMetaService
     */
    public function __construct(LayoutServiceInterface $pdfSizes, TypographyServiceInterface $typographyService, PathServiceInterface $pathService, PrintMetaServiceInterface $printMetaService)
    {
        $this->layoutService = $pdfSizes;
        $this->typographyService = $typographyService;
        $this->pathService = $pathService;
        $this->printMetaService = $printMetaService;

        $this->copyFontsIfNeeded();
    }

    /**
     * tcpdf uses an internal font format
     * we need to copy our prepared resource files to the correct location.
     */
    private function copyFontsIfNeeded()
    {
        //prepare fonts
        $checkFilePath = K_PATH_FONTS . '/.copied2';
        if (!file_exists($checkFilePath)) {
            $sourceFolder = $this->pathService->getAssetsRoot() . \DIRECTORY_SEPARATOR . 'report' . \DIRECTORY_SEPARATOR . 'fonts';
            //copy all fonts from the assets to the fonts folder of tcpdf
            shell_exec('\cp -r ' . $sourceFolder . '/* ' . K_PATH_FONTS);
            file_put_contents($checkFilePath, time());
        }
    }

    /**
     * @param Pdf $pdf
     */
    public function initialize(Pdf $pdf)
    {
        $this->printMetaService->initializeLayout($pdf);
    }

    /**
     * @param Pdf $pdf
     * @param string $headerLeft
     * @param string $footerLeft
     * @param string $logoPath
     */
    public function setMeta(Pdf $pdf, string $headerLeft, string $footerLeft, string $logoPath)
    {
        $this->headerLeft = $headerLeft;
        $this->footerLeft = $footerLeft;
        $this->logoPath = $logoPath;

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($footerLeft);
        $pdf->SetTitle($headerLeft);
    }

    /**
     * @param Pdf $pdf
     * @param string $title
     * @param string $logoPath
     */
    public function printHeader(Pdf $pdf)
    {
        $this->printMetaService->printHeaderLeft($pdf, $this->headerLeft);
        $this->printMetaService->printLogo($pdf, $this->logoPath);
    }

    /**
     * @param Pdf $pdf
     * @param string $author
     */
    public function printFooter(Pdf $pdf)
    {
        $this->printMetaService->printFooterLeft($pdf, $this->footerLeft);
        $this->printMetaService->printPageNumbers($pdf);
    }
}
