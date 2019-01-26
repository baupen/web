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
use App\Service\Report\Pdf\Interfaces\PageLayoutServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Tcpdf\Interfaces\TcpdfServiceInterface;

/**
 * Functionality needed by the TCPDF object
 * To enable dependency injection & testing this is in this service and not in the TCPDF object directly.
 *
 * Class TcpdfService
 */
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
    }

    /**
     * sets the global variables needed for TCPDF.
     */
    public function initializeGlobalVariables()
    {
        if (!\defined('K_PATH_FONTS')) {
            // TCPDF looks at the path this global variable defines
            \define('K_PATH_FONTS', $this->pathService->getAssetsRoot() . \DIRECTORY_SEPARATOR . 'report' . \DIRECTORY_SEPARATOR . 'fonts' . \DIRECTORY_SEPARATOR);
        }

        if (!\defined('PDF_FONT_NAME_MAIN')) {
            // TCPDF chooses this font as the default
            \define('PDF_FONT_NAME_MAIN', 'opensans');
        }

        if (!\defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
            // TCPDF chooses this font as the default
            \define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
        }

        if (!\defined('K_TCPDF_EXTERNAL_CONFIG')) {
            \define('K_TCPDF_EXTERNAL_CONFIG', true);
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
     * @param Pdf $document
     * @param string $headerLeft
     * @param string $footerLeft
     * @param string $logoPath
     */
    public function setPageVariables(Pdf $document, string $headerLeft, string $footerLeft, string $logoPath)
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
        $identifier = $pdf->getIdentifier();
        $document = $this->pdfDocumentDictionary[$identifier];

        $headerLeft = $this->pdfMetaDictionary[$identifier]['headerLeft'];
        $this->pageLayoutService->printHeaderLeft($document, $headerLeft);

        $logoPath = $this->pdfMetaDictionary[$identifier]['logoPath'];
        $this->pageLayoutService->printLogo($document, $logoPath);
    }

    /**
     * @param Pdf $pdf
     * @param int $currentPageNumber
     * @param int $totalPageNumbers
     */
    public function printFooter(Pdf $pdf, int $currentPageNumber, int $totalPageNumbers)
    {
        $identifier = $pdf->getIdentifier();
        $document = $this->pdfDocumentDictionary[$identifier];

        $footerLeft = $this->pdfMetaDictionary[$identifier]['footerLeft'];
        $this->pageLayoutService->printFooterLeft($document, $footerLeft);

        $this->pageLayoutService->printPageNumbers($document, $currentPageNumber, $totalPageNumbers);
    }
}
