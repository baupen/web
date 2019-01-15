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

use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class PrintBuffer
{
    /**
     * @var callable[]
     */
    private $buffer;

    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * PrintBuffer constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     */
    public function __construct(PdfDocumentInterface $pdfDocument)
    {
        $this->pdfDocument = $pdfDocument;
    }

    /**
     * @return \Closure
     */
    public function invokePrintablesCallable()
    {
        $buffer = $this->buffer;

        return function () use ($buffer) {
            foreach ($buffer as $item) {
                $item();
            }
        };
    }

    /**
     * @param callable $callable
     * @param array $callableArgs
     */
    public function bufferPrintable(callable $callable, array $callableArgs)
    {
        $pdfDocument = $this->pdfDocument;

        $printConfig = $pdfDocument->getPrintConfiguration();
        $cursor = $pdfDocument->getCursor();

        $this->buffer[] = function () use ($pdfDocument, $callable, $callableArgs, $printConfig, $cursor) {
            $pdfDocument->setCursor($cursor);
            $pdfDocument->setPrintConfiguration($printConfig);

            $callable($pdfDocument, ...$callableArgs);
        };
    }
}
