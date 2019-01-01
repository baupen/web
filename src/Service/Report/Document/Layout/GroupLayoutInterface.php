<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Layout;

use App\Service\Report\Document\PrinterInterface;

interface GroupLayoutInterface extends PrinterInterface
{
    /**
     * will end the columned layout.
     */
    public function endLayout();
}
