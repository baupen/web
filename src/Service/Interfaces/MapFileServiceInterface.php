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

interface MapFileServiceInterface
{
    /**
     * @param Map $entity
     *
     * @return string|null
     */
    public function getForMobileDevice(Map $entity);
}
