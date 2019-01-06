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

use App\Service\Report\Document\Interfaces\Configuration\Table;
use App\Service\Report\Document\Interfaces\Configuration\TableColumn;
use App\Service\Report\Document\Interfaces\Layout\ColumnLayoutInterface;
use App\Service\Report\Document\Interfaces\Layout\FullWidthLayoutInterface;
use App\Service\Report\Document\Interfaces\Layout\GroupLayoutInterface;
use App\Service\Report\Document\Interfaces\Layout\TableLayoutInterface;
use App\Service\Report\Document\Interfaces\LayoutFactoryInterface;
use App\Service\Report\Pdf\Design\Interfaces\ColorServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Layout\ColumnLayout;
use App\Service\Report\Pdf\Layout\FullWidthLayout;
use App\Service\Report\Pdf\Layout\GroupLayout;
use App\Service\Report\Pdf\Layout\TableLayout;

class LayoutFactory implements LayoutFactoryInterface
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
     * Document constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param LayoutServiceInterface $layoutService
     * @param TypographyServiceInterface $typographyService
     * @param ColorServiceInterface $colorService
     */
    public function __construct(PdfDocumentInterface $pdfDocument, LayoutServiceInterface $layoutService, TypographyServiceInterface $typographyService, ColorServiceInterface $colorService)
    {
        $this->document = $pdfDocument;
        $this->layoutService = $layoutService;
        $this->typographyService = $typographyService;
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
        return new GroupLayout($this->document, $this->layoutService->getContentXSize());
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
        return new ColumnLayout($this->document, $columnCount, $this->layoutService->getColumnGutter(), $this->layoutService->getContentXSize());
    }

    /**
     * starts a table.
     *
     * @param Table $table
     * @param TableColumn[] $tableColumns
     *
     * @return TableLayoutInterface
     */
    public function createTableLayout(Table $table, array $tableColumns)
    {
        return new TableLayout($this->document, $this->layoutService->getContentXSize(), $this->layoutService->getTableColumnGutter(), $table, $tableColumns);
    }

    /**
     * starts a region with 100% width.
     *
     * @return FullWidthLayoutInterface
     */
    public function createFullWidthLayout()
    {
        return new FullWidthLayout($this->document, $this->layoutService->getContentXSize());
    }
}
