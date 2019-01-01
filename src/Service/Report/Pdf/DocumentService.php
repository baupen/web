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
use App\Service\Report\Document\DocumentInterface;
use App\Service\Report\Document\Interfaces\DocumentServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Document\Document;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentServiceInterface;
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
     * @var TypographyServiceInterface
     */
    private $typographyService;

    /**
     * @var LayoutServiceInterface
     */
    private $layoutService;

    /**
     * DocumentService constructor.
     *
     * @param TranslatorInterface $translator
     * @param PdfDocumentServiceInterface $pdfDocumentService
     * @param TypographyServiceInterface $typographyService
     * @param LayoutServiceInterface $layoutService
     */
    public function __construct(TranslatorInterface $translator, PdfDocumentServiceInterface $pdfDocumentService, TypographyServiceInterface $typographyService, LayoutServiceInterface $layoutService)
    {
        $this->translator = $translator;
        $this->pdfDocumentService = $pdfDocumentService;
        $this->typographyService = $typographyService;
        $this->layoutService = $layoutService;
    }

    /**
     * @param string $title
     * @param string $author
     *
     * @throws \Exception
     *
     * @return DocumentInterface
     */
    public function create(string $title, string $author)
    {
        $footer = $this->translator->trans('generated', ['%date%' => (new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT), '%name%' => $author], 'report');
        $logoPath = $this->pathService->getAssetsRoot() . 'report' . \DIRECTORY_SEPARATOR . 'logo.png';
        $pdfDocument = $this->pdfDocumentService->create($title, $footer, $logoPath);

        $pdfDocument->setMeta($title, $author);

        return new Document($pdfDocument, $this->layoutService, $this->typographyService);
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
