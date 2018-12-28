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

class Color
{
    /**
     * @var int[]
     */
    private $textColor = [37, 40, 32];

    /**
     * @var int[]
     */
    private $secondaryTextColor = [68, 73, 58];

    /**
     * @var int[]
     */
    private $drawColor = [200, 200, 200];

    /**
     * @var int[]
     */
    private $background = [230, 230, 230];

    /**
     * @var int[]
     */
    private $secondaryBackground = [240, 240, 240];
}
