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
use App\Service\Report\CleanPdf;
use App\Service\Report\Pdf\Interfaces\DocumentInterface;
use App\Service\Report\Pdf\Interfaces\DocumentServiceInterface;
use App\Service\Report\Pdf\Interfaces\TcpdfServiceInterface;
use App\Service\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class DocumentService implements DocumentServiceInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PathServiceInterface $pathService
     */
    private $pathService;

    /**
     * @var TcpdfServiceInterface $pathService
     */
    private $tcpdfService;

    /**
     * DocumentService constructor.
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
     * @return DocumentInterface
     * @throws \Exception
     */
    public function create(string $title, string $author)
    {
        $pdf = new CleanPdf($this->tcpdfService);
        $pdf->setMeta($title, $author, );
        $footnote = $this->translator->trans('generated', ['%date%' => (new \DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT), '%name%' => $author], 'report');
        $pdfDefinition = new PdfDefinition($constructionSite->getName(), $footnote, __DIR__ . '/../../public/files/report_logo.png');
        $report = new Report($pdfDefinition);

    }

    /**
     * @param DocumentInterface $pdfDocument
     * @param string $savePath
     */
    public function save(DocumentInterface $pdfDocument, string $savePath)
    {
        // TODO: Implement saveDocument() method.
    }
}
