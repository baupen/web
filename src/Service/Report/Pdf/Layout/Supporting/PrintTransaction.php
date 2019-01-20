<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Layout\Supporting;

use App\Service\Report\Document\Interfaces\Layout\Base\PrintTransactionInterface;
use App\Service\Report\Pdf\Cursor;
use App\Service\Report\Pdf\Interfaces\PdfDocument\PdfDocumentTransactionInterface;

class PrintTransaction implements PrintTransactionInterface
{
    /**
     * @var \Closure
     */
    private $content;

    /**
     * @var PdfDocumentTransactionInterface
     */
    private $pdfDocument;

    /**
     * @var float
     */
    private $width;

    /**
     * @var callable
     */
    private $onPreCommit;

    /**
     * @var callable
     */
    private $onPostCommit;

    /**
     * PrintBuffer constructor.
     *
     * @param PdfDocumentTransactionInterface $pdfDocument
     * @param float $width
     * @param \Closure $content
     */
    public function __construct(PdfDocumentTransactionInterface $pdfDocument, float $width, \Closure $content)
    {
        $this->pdfDocument = $pdfDocument;
        $this->width = $width;

        $this->content = $content;
    }

    /**
     * @param callable $callable
     */
    public function setOnPreCommit(callable $callable): void
    {
        $this->onPreCommit = $callable;
    }

    /**
     * @param callable $callable
     */
    public function setOnPostCommit(callable $callable): void
    {
        $this->onPostCommit = $callable;
    }

    /**
     * get the area of the to-be printed area by this transaction
     * returns an array where the first entry is the start cursor; the second the end cursor.
     *
     * @return Cursor[]
     */
    public function calculatePrintArea()
    {
        $emptyBuffer = $this->getCommitClosure();

        $before = $this->pdfDocument->getCursor();
        $after = $this->pdfDocument->cursorAfterwardsIfPrinted($emptyBuffer);

        $after->setX($before->getXCoordinate() + $this->width);

        return [$before, $after];
    }

    /**
     * prints the contained components.
     */
    public function commit()
    {
        $this->getCommitClosure()();
    }

    /**
     * @return \Closure
     */
    private function getCommitClosure(): \Closure
    {
        return function () {
            $preCommit = $this->onPreCommit;
            if ($preCommit !== null) {
                $preCommit();
            }

            $flushBuffer = $this->content;
            $flushBuffer();

            $postCommit = $this->onPostCommit;
            if ($postCommit !== null) {
                $postCommit();
            }
        };
    }
}
