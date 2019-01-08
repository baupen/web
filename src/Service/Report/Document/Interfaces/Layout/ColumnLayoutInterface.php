<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Interfaces\Layout;

use App\Service\Report\Document\Interfaces\Layout\Base\LayoutInterface;
use App\Service\Report\Pdf\Interfaces\PrintableLayoutInterface;

interface ColumnLayoutInterface extends LayoutInterface, PrintableLayoutInterface
{
    /**
     * ensures the next printed elements are printed in the specified column
     * will throw an exception if the column region does not exist.
     *
     * @param int $column
     */
    public function goToColumn(int $column);

    /**
     * when printing something, the column with the least content is chosen automatically.
     *
     * @param bool $active
     */
    public function setAutoColumn(bool $active);
}
