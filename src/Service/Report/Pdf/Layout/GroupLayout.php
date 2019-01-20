<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Layout;

use App\Service\Report\Document\Interfaces\Layout\Base\PrintTransactionInterface;
use App\Service\Report\Document\Interfaces\Layout\GroupLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocument\PdfDocumentTransactionInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Layout\Supporting\PrintBuffer;
use App\Service\Report\Pdf\Layout\Supporting\PrintTransaction;

class GroupLayout implements GroupLayoutInterface
{
    /**
     * @var PdfDocumentTransactionInterface
     */
    private $pdfDocument;

    /**
     * @var float
     */
    private $width;

    /**
     * @var PrintBuffer
     */
    private $printBuffer;

    /**
     * ColumnLayout constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param float $width
     */
    public function __construct(PdfDocumentInterface $pdfDocument, float $width)
    {
        $this->pdfDocument = $pdfDocument;
        $this->width = $width;

        $this->printBuffer = new PrintBuffer($pdfDocument, $width);
    }

    /**
     * will end the columned layout.
     *
     * @return PrintTransactionInterface
     */
    public function getTransaction()
    {
        $printContent = $this->printBuffer->flushBufferClosure();
        $transaction = new PrintTransaction($this->pdfDocument, $this->width, $printContent);

        // start new page if needed
        [$start, $end] = $transaction->calculatePrintArea();
        if ($start->getPage() !== $end->getPage()) {
            $transaction->setOnPreCommit(function (PdfDocumentInterface $pdfDocument) {
                $pdfDocument->startNewPage();
            });
        }

        return $transaction;
    }

    /**
     * register a callable which prints to the pdf document
     * The position of the cursor at the time the callable is invoked is decided by the layout
     * ensure the cursor is below the printed content after the callable is finished to not mess up the layout.
     *
     * @param callable $callable takes a PdfDocumentInterface as an argument
     */
    public function registerPrintable(callable $callable)
    {
        $this->printBuffer->addPrintable($callable);
    }
}
