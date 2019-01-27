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

use App\Service\Report\Document\Layout\Base\PrintableLayoutInterface;
use App\Service\Report\Document\Pdf\PdfPageLayoutInterface;
use App\Service\Report\Document\Transaction\Base\DrawableTransactionInterface;
use App\Service\Report\IssueReport\Model\MetaData;

interface PrintFactoryInterface
{
    /**
     * @param MetaData $pageLayoutContent
     *
     * @return PdfPageLayoutInterface
     */
    public function getLayout(MetaData $pageLayoutContent);

    /**
     * @param PrintableLayoutInterface $printableLayout
     *
     * @return PrinterInterface
     */
    public function getPrinter(PrintableLayoutInterface $printableLayout);

    /**
     * @param DrawableTransactionInterface $drawableTransaction
     *
     * @return DrawerInterface
     */
    public function getDrawer(DrawableTransactionInterface $drawableTransaction);
}
