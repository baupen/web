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

interface TableLayoutInterface extends LayoutInterface
{
    /**
     * @param string[] $header
     */
    public function printHeader(array $header);

    /**
     * @param string[] $row
     *
     * @return mixed
     */
    public function printRow(array $row);
}
