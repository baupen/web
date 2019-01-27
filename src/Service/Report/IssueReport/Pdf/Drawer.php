<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Pdf;

use App\Service\Report\Document\Pdf\Configuration\DrawConfiguration;
use App\Service\Report\Document\Pdf\Cursor;
use App\Service\Report\Document\Pdf\PdfDocumentInterface;
use App\Service\Report\Document\Transaction\Base\DrawableTransactionInterface;
use App\Service\Report\IssueReport\Interfaces\DrawerInterface;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\ColorServiceInterface;

class Drawer implements DrawerInterface
{
    /**
     * @var DrawableTransactionInterface
     */
    private $transaction;

    /**
     * @var ColorServiceInterface
     */
    private $color;

    /**
     * Drawer constructor.
     *
     * @param DrawableTransactionInterface $drawableTransaction
     * @param ColorServiceInterface $colorService
     */
    public function __construct(DrawableTransactionInterface $drawableTransaction, ColorServiceInterface $colorService)
    {
        $this->transaction = $drawableTransaction;
        $this->color = $colorService;
    }

    public function drawTableAlternatingBackground()
    {
        $this->transaction->registerDrawablePrePrint(function (PdfDocumentInterface $document, Cursor $end) {
            $document->configure([DrawConfiguration::FILL_COLOR => $this->color->getTableAlternatingBackgroundColor()]);
            $document->drawUntil($end);
        });
    }
}
