<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Interfaces;

use App\Service\Report\IssueReport\Model\MetaData;
use PdfGenerator\Layout\Base\PrintableLayoutInterface;
use PdfGenerator\Pdf\PdfPageLayoutInterface;
use PdfGenerator\Transaction\Base\DrawableTransactionInterface;

interface PrintFactoryInterface
{
    /**
     * @return PdfPageLayoutInterface
     */
    public function getLayout(MetaData $pageLayoutContent);

    /**
     * @return PrinterInterface
     */
    public function getPrinter(PrintableLayoutInterface $printableLayout);

    /**
     * @return DrawerInterface
     */
    public function getDrawer(DrawableTransactionInterface $drawableTransaction);
}
