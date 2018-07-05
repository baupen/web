<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\Map;

interface ImageServiceInterface
{
    /**
     * @param Map $map
     * @param array $issues
     *
     * @return string
     */
    public function generateMapImage(Map $map, array $issues);
}
