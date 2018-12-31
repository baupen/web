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

use App\Service\Report\Document\Interfaces\PrintServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Design\TypographyService;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class PrintService implements PrintServiceInterface
{
    /**
     * @var PdfDocumentInterface
     */
    private $document;

    /**
     * @var TypographyService
     */
    private $typography;

    /**
     * @param PdfDocumentInterface $pdfDocument
     */
    public function __construct(PdfDocumentInterface $pdfDocument, TypographyServiceInterface $typographyService)
    {
        $this->document = $pdfDocument;
        $this->typography = $typographyService;
    }

    /**
     * @param string $paragraph
     */
    public function printParagraph(string $paragraph)
    {
        $this->document->printText($paragraph, $this->typography->getTextFontSize());
    }

    /**
     * @param string $header
     */
    public function printRegionHeader(string $header)
    {
        // TODO: Implement printRegionHeader() method.
    }

    /**
     * @param string[] $header
     * @param string[][] $content
     */
    public function printTable(array $header, array $content)
    {
        // TODO: Implement printTable() method.
    }

    /**
     * @param string[] $header
     * @param string[][] $content
     */
    public function printImage(array $header, array $content)
    {
        // TODO: Implement printImage() method.
    }

    /**
     * ends the active region & adds appropriate spacing.
     */
    public function endRegion()
    {
        // TODO: Implement endRegion() method.
    }

    /**
     * will avoid a page break between the next printed elements
     * will add a page break before all elements if they do not fit on the same page
     * active until end region is called.
     */
    public function startGroupedRegion()
    {
        // TODO: Implement startGroupedRegion() method.
    }

    /**
     * starts a region with columns.
     *
     * @param int $columnCount
     */
    public function startColumnedRegion(int $columnCount)
    {
        // TODO: Implement startColumnedRegion() method.
    }

    /**
     * ensures the next printed elements are printed in the specified column
     * will throw an exception if the column region does not exist.
     *
     * @param int $column
     */
    public function goToColumn(int $column)
    {
        // TODO: Implement goToColumn() method.
    }
}
