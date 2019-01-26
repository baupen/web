<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport;

use App\Service\Report\Document\Interfaces\Layout\Base\PrintableLayoutInterface;
use App\Service\Report\Document\Transaction\Base\DrawableTransactionInterface;
use App\Service\Report\IssueReport\Interfaces\DrawerInterface;
use App\Service\Report\IssueReport\Interfaces\PrinterInterface;
use App\Service\Report\IssueReport\Interfaces\PrintFactoryInterface;
use App\Service\Report\IssueReport\Pdf\Drawer;
use App\Service\Report\IssueReport\Pdf\Printer;
use App\Service\Report\Pdf\Design\Interfaces\ColorServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Design\TypographyService;

class PrintFactory implements PrintFactoryInterface
{
    /**
     * @var TypographyService
     */
    private $typography;

    /**
     * @var ColorServiceInterface
     */
    private $color;

    /**
     * @param TypographyServiceInterface $typographyService
     * @param ColorServiceInterface $colorService
     */
    public function __construct(TypographyServiceInterface $typographyService, ColorServiceInterface $colorService)
    {
        $this->typography = $typographyService;
        $this->color = $colorService;
    }

    /**
     * @param PrintableLayoutInterface $printableLayout
     *
     * @return PrinterInterface
     */
    public function getPrinter(PrintableLayoutInterface $printableLayout)
    {
        return new Printer($printableLayout, $this->typography, $this->color);
    }

    /**
     * @param DrawableTransactionInterface $drawableTransaction
     *
     * @return DrawerInterface
     */
    public function getDrawer(DrawableTransactionInterface $drawableTransaction)
    {
        return new Drawer($drawableTransaction, $this->color);
    }
}
