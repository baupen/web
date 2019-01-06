<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Layout\Base;

use App\Service\Report\Document\Interfaces\Layout\LayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PrintableProducerInterface;

abstract class BaseLayout implements LayoutInterface, PdfLayoutInterface
{
    /**
     * @var PrintableProducerInterface
     */
    private $printer;

    /**
     * BaseLayout constructor.
     *
     * @param PrintableProducerInterface $printableProducer
     */
    protected function __construct(PrintableProducerInterface $printableProducer)
    {
        $this->printer = $printableProducer;
    }

    /**
     * @return PrintableProducerInterface
     */
    public function getPrinterProducer()
    {
        return $this->printer;
    }
}
