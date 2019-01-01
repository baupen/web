<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Document;

use App\Service\Report\Document\DocumentInterface;
use App\Service\Report\Document\Layout\ColumnLayoutInterface;
use App\Service\Report\Document\Layout\GroupLayoutInterface;
use App\Service\Report\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Document\Layout\ColumnLayout;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Printer;

class Document implements DocumentInterface
{
    /**
     * @var PdfDocumentInterface
     */
    private $document;

    /**
     * @var LayoutServiceInterface
     */
    private $layoutService;

    /**
     * @var TypographyServiceInterface
     */
    private $typographyService;

    /**
     * @var Printer
     */
    private $printer;

    /**
     * Document constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param LayoutServiceInterface $layoutService
     * @param TypographyServiceInterface $typographyService
     */
    public function __construct(PdfDocumentInterface $pdfDocument, LayoutServiceInterface $layoutService, TypographyServiceInterface $typographyService)
    {
        $this->document = $pdfDocument;
        $this->layoutService = $layoutService;
        $this->typographyService = $typographyService;

        $this->printer = new Printer($pdfDocument, $this->typographyService);
    }

    /**
     * will avoid a page break between the next printed elements
     * will add a page break before all elements if they do not fit on the same page
     * active until end region is called.
     *
     * @return GroupLayoutInterface
     */
    public function createGroupLayout()
    {
    }

    /**
     * starts a region with columns.
     *
     * @param int $columnCount
     *
     * @return ColumnLayoutInterface
     */
    public function createColumnLayout(int $columnCount)
    {
        return new ColumnLayout($this->printer, $this->document, $columnCount, $this->layoutService->getColumnGutter(), $this->layoutService->getContentXSize());
    }
}
