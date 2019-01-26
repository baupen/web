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

use App\Service\Report\Document\Transaction\Base\DrawableTransactionInterface;
use App\Service\Report\IssueReport\Interfaces\DrawerInterface;
use App\Service\Report\Pdf\Area;
use App\Service\Report\Pdf\Design\Interfaces\ColorServiceInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocument\PdfDocumentDrawInterface;

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
     */
    public function __construct(DrawableTransactionInterface $drawableTransaction, ColorServiceInterface $colorService)
    {
        $this->transaction = $drawableTransaction;
        $this->color = $colorService;
    }

    public function drawTableAlternatingBackground()
    {
        $this->transaction->registerDrawablePrePrint(function (PdfDocumentDrawInterface $document, Area $printArea) {
            $document->configureDraw(['background_color' => $this->color->getTableAlternatingBackgroundColor()]);
            $document->drawArea($printArea);
        });
    }
}
