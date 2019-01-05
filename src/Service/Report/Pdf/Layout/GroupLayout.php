<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Document\Layout;

use App\Service\Report\Document\Interfaces\Layout\GroupLayoutInterface;
use App\Service\Report\Pdf\Document\Layout\Base\BaseLayout;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\IssueReportPdfConventions;

class GroupLayout extends BaseLayout implements GroupLayoutInterface
{
    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * @var float
     */
    private $width;

    /**
     * @var \Closure[]
     */
    private $buffer = [];

    /**
     * ColumnLayout constructor.
     *
     * @param IssueReportPdfConventions $printer
     * @param PdfDocumentInterface $pdfDocument
     * @param float $width
     */
    public function __construct(IssueReportPdfConventions $printer, PdfDocumentInterface $pdfDocument, float $width)
    {
        parent::__construct($printer, $width);

        $this->pdfDocument = $pdfDocument;
        $this->width = $width;
    }

    /**
     * @param string $title
     */
    public function printTitle(string $title)
    {
        $this->buffer[] = function () use ($title) {
            parent::printTitle($title);
        };
    }

    /**
     * @param string $paragraph
     */
    public function printParagraph(string $paragraph)
    {
        $this->buffer[] = function () use ($paragraph) {
            parent::printParagraph($paragraph);
        };
    }

    /**
     * @param string[] $keyValues
     */
    public function printKeyValueParagraph(array $keyValues)
    {
        $this->buffer[] = function () use ($keyValues) {
            parent::printKeyValueParagraph($keyValues);
        };
    }

    /**
     * @param string $header
     */
    public function printRegionHeader(string $header)
    {
        $this->buffer[] = function () use ($header) {
            parent::printRegionHeader($header);
        };
    }

    /**
     * @param string $filePath
     */
    public function printImage(string $filePath)
    {
        $this->buffer[] = function () use ($filePath) {
            parent::printImage($filePath);
        };
    }

    /**
     * will end the columned layout.
     */
    public function endLayout()
    {
        $emptyBuffer = function () {
            foreach ($this->buffer as $item) {
                $item();
            }
        };

        if ($this->pdfDocument->causesPageBreak($emptyBuffer)) {
            $this->pdfDocument->startNewPage();
        }

        $emptyBuffer();
    }
}
