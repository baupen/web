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
use App\Service\Report\Document\Interfaces\PrintServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentServiceInterface;
use App\Service\Report\Pdf\Tcpdf\Interfaces\TcpdfServiceInterface;
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
     * @var PdfDocumentServiceInterface
     */
    private $pdfDocumentService;

    /**
     * DocumentService constructor.
     *
     * @param TranslatorInterface $translator
     * @param PathServiceInterface $pathService
     * @param TcpdfServiceInterface $tcpdfService
     */
    public function __construct(TranslatorInterface $translator, PathServiceInterface $pathService, PdfDocumentServiceInterface $pdfDocumentService)
    {
        $this->translator = $translator;
        $this->pdfDocumentService = $pdfDocumentService;
    }

    /**
     * @param string $title
     * @param string $author
     *
     * @throws \Exception
     *
     * @return PrintServiceInterface
     */
    public function create(string $title, string $author)
    {
        $footer = $this->translator->trans('generated', ['%date%' => (new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT), '%name%' => $author], 'report');
        $logoPath = $this->pathService->getAssetsRoot() . 'report' . \DIRECTORY_SEPARATOR . 'logo.png';
        $document = $this->pdfDocumentService->create($title, $footer, $logoPath);

        $document->setMeta($title, $author);

        $printService = new PrintService();
        $printService->setDocument($document);

        return $printService;
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
