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

use App\Helper\DateTimeFormatter;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Report\Document\Interfaces\DocumentServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Tcpdf\Interfaces\TcpdfServiceInterface;
use App\Service\Report\Pdf\Tcpdf\Pdf;
use App\Service\Report\Pdf\Tcpdf\PdfDocument;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentService implements DocumentServiceInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var TcpdfServiceInterface
     */
    private $tcpdfService;

    /**
     * DocumentService constructor.
     *
     * @param TranslatorInterface $translator
     * @param PathServiceInterface $pathService
     * @param TcpdfServiceInterface $tcpdfService
     */
    public function __construct(TranslatorInterface $translator, PathServiceInterface $pathService, TcpdfServiceInterface $tcpdfService)
    {
        $this->translator = $translator;
        $this->pathService = $pathService;
        $this->tcpdfService = $tcpdfService;
    }

    /**
     * @param string $title
     * @param string $author
     *
     * @throws \Exception
     *
     * @return PdfDocumentInterface
     */
    public function create(string $title, string $author)
    {
        $pdfDocument = $this->createPdfDocument();
        $pdfDocument->setMeta($title, $author);

        $footerLeft = $this->translator->trans('generated', ['%date%' => (new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT), '%name%' => $author], 'report');
        $logoPath = $this->pathService->getAssetsRoot() . 'report' . \DIRECTORY_SEPARATOR . 'logo.png';
        $this->tcpdfService->setPageVariables($pdfDocument, $title, $footerLeft, $logoPath);

        return $pdfDocument;
    }

    /**
     * @return PdfDocumentInterface
     */
    private function createPdfDocument()
    {
        $pdf = new Pdf($this->tcpdfService);

        $wrapper = new PdfDocument($pdf);
        $this->tcpdfService->assignWrapper($pdf, $wrapper);

        return $wrapper;
    }

    /**
     * @param PdfDocumentInterface $pdfDocument
     * @param string $savePath
     */
    public function save(PdfDocumentInterface $pdfDocument, string $savePath)
    {
        // TODO: Implement saveDocument() method.
    }
}
