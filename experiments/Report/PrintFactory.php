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

use App\Service\Report\IssueReport\Interfaces\DrawerInterface;
use App\Service\Report\IssueReport\Interfaces\PrinterInterface;
use App\Service\Report\IssueReport\Interfaces\PrintFactoryInterface;
use App\Service\Report\IssueReport\Model\MetaData;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\ColorServiceInterface;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\LayoutServiceInterface;
use App\Service\Report\IssueReport\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\IssueReport\Pdf\Design\TypographyService;
use App\Service\Report\IssueReport\Pdf\Drawer;
use App\Service\Report\IssueReport\Pdf\PdfPageLayout;
use App\Service\Report\IssueReport\Pdf\Printer;
use PdfGenerator\Layout\Base\PrintableLayoutInterface;
use PdfGenerator\Transaction\Base\DrawableTransactionInterface;

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

    public function __construct(TypographyServiceInterface $typographyService, ColorServiceInterface $colorService, LayoutServiceInterface $layoutService)
    {
        $this->typography = $typographyService;
        $this->color = $colorService;
        $this->layout = $layoutService;
    }

    /**
     * @return PdfPageLayout
     */
    public function getLayout(MetaData $pageLayoutContent)
    {
        return new PdfPageLayout($this->layout, $this->typography, $pageLayoutContent);
    }

    /**
     * @return PrinterInterface
     */
    public function getPrinter(PrintableLayoutInterface $printableLayout)
    {
        return new Printer($printableLayout, $this->typography, $this->color);
    }

    /**
     * @return DrawerInterface
     */
    public function getDrawer(DrawableTransactionInterface $drawableTransaction)
    {
        return new Drawer($drawableTransaction, $this->color);
    }
}
