<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Interfaces;

use App\Service\Report\Document\Interfaces\Configuration\Table;
use App\Service\Report\Document\Interfaces\Configuration\TableColumn;
use App\Service\Report\Document\Interfaces\Layout\ColumnLayoutInterface;
use App\Service\Report\Document\Interfaces\Layout\FullWidthLayoutInterface;
use App\Service\Report\Document\Interfaces\Layout\GroupLayoutInterface;
use App\Service\Report\Document\Interfaces\Layout\TableLayoutInterface;

interface DocumentLayoutInterface
{
    /**
     * starts a region with 100% width.
     *
     * @return FullWidthLayoutInterface
     */
    public function createFullWidthLayout();

    /**
     * will avoid a page break between the next printed elements
     * will add a page break before all elements if they do not fit on the same page
     * active until end region is called.
     *
     * @return GroupLayoutInterface
     */
    public function createGroupLayout();

    /**
     * starts a region with columns.
     *
     * @param int $columnCount
     *
     * @return ColumnLayoutInterface
     */
    public function createColumnLayout(int $columnCount);

    /**
     * starts a table.
     *
     * @param Table $table
     * @param TableColumn[] $tableColumns
     *
     * @return TableLayoutInterface
     */
    public function createTableLayout(Table $table, array $tableColumns);
}
