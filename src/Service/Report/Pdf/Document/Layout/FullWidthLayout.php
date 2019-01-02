<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Document\Layout;

use App\Service\Report\Document\Interfaces\Layout\FullWidthLayoutInterface;
use App\Service\Report\Pdf\Document\Layout\Base\BaseLayout;
use App\Service\Report\Pdf\Document\PdfPrinter;

class FullWidthLayout extends BaseLayout implements FullWidthLayoutInterface
{
    /**
     * ColumnLayout constructor.
     *
     * @param PdfPrinter $printer
     * @param float $width
     */
    public function __construct(PdfPrinter $printer, float $width)
    {
        parent::__construct($printer, $width);
    }

    /**
     * will end the columned layout.
     */
    public function endLayout()
    {
        // no need to act here
    }
}
