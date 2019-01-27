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
use App\Service\Report\IssueReport\Model\MetaData;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\ColorServiceInterface;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\IssueReport\Pdf\Design\TypographyService;
use App\Service\Report\IssueReport\Pdf\Drawer;
use App\Service\Report\IssueReport\Pdf\PageLayout;
use App\Service\Report\IssueReport\Pdf\Printer;

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
     * @var LayoutServiceInterface
     */
    private $layout;

    /**
     * @param TypographyServiceInterface $typographyService
     * @param ColorServiceInterface $colorService
     */
    public function __construct(TypographyServiceInterface $typographyService, ColorServiceInterface $colorService, LayoutServiceInterface $layoutService)
    {
        $this->typography = $typographyService;
        $this->color = $colorService;
        $this->layout = $layoutService;
    }

    /**
     * @param MetaData $pageLayoutContent
     *
     * @return PageLayout
     */
    public function getLayout(MetaData $pageLayoutContent)
    {
        return new PageLayout($this->layout, $this->typography, $pageLayoutContent);
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
