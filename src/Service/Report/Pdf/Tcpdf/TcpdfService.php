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

use App\Service\Interfaces\PathServiceInterface;
use App\Service\Report\Document\Interfaces\PageLayoutServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Tcpdf\Interfaces\TcpdfServiceInterface;

class TcpdfService implements TcpdfServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var PageLayoutServiceInterface
     */
    private $pageLayoutService;

    /**
     * @var PdfDocumentInterface[]
     */
    private $pdfDocumentDictionary;

    /**
     * @var array
     */
    private $pdfMetaDictionary;

    /**
     * @param PathServiceInterface $pathService
     * @param PageLayoutServiceInterface $pageLayoutService
     */
    public function __construct(PathServiceInterface $pathService, PageLayoutServiceInterface $pageLayoutService)
    {
        $this->pathService = $pathService;
        $this->pageLayoutService = $pageLayoutService;

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
     * @param PdfDocumentInterface $document
     */
    public function assignWrapper(Pdf $pdf, PdfDocumentInterface $document)
    {
        $this->pdfDocumentDictionary[$pdf->getIdentifier()] = $document;
        $this->pageLayoutService->initializeLayout($document);
    }

    /**
     * @param PdfDocumentInterface $document
     * @param string $headerLeft
     * @param string $footerLeft
     * @param string $logoPath
     */
    public function setPageVariables(PdfDocumentInterface $document, string $headerLeft, string $footerLeft, string $logoPath)
    {
        $this->pdfMetaDictionary[$document->getIdentifier()] = [
            'headerLeft' => $headerLeft,
            'footerLeft' => $footerLeft,
            'logoPath' => $logoPath,
        ];
    }

    /**
     * @param Pdf $pdf
     */
    public function printHeader(Pdf $pdf)
    {
        $document = $this->pdfDocumentDictionary[$pdf->getIdentifier()];

        $headerLeft = $this->pdfMetaDictionary[$document->getIdentifier()]['headerLeft'];
        $this->pageLayoutService->printHeaderLeft($document, $headerLeft);

        $logoPath = $this->pdfMetaDictionary[$document->getIdentifier()]['logoPath'];
        $this->pageLayoutService->printLogo($document, $logoPath);
    }

    /**
     * @param Pdf $pdf
     * @param int $currentPageNumber
     * @param int $totalPageNumbers
     */
    public function printFooter(Pdf $pdf, int $currentPageNumber, int $totalPageNumbers)
    {
        $document = $this->pdfDocumentDictionary[$pdf->getIdentifier()];

        $footerLeft = $this->pdfMetaDictionary[$document->getIdentifier()]['footerLeft'];
        $this->pageLayoutService->printFooterLeft($document, $footerLeft);

        $this->pageLayoutService->printPageNumbers($document, $currentPageNumber, $totalPageNumbers);
    }
}
