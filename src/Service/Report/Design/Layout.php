<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Design;

class Layout
{
    /**
     * @var float the used page size
     */
    private $pageSize = [210, 297];

    /**
     * @var float[]
     *              margins of the page; left, top, right, bottom
     */
    private $pageMargins = [10, 6, 10, 6];

    /**
     * @var float
     */
    private $baseSpacing = 8;

    /**
     * @var float
     */
    private $scalingFactor = 1.6;
}
