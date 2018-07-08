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
    const SIZE_THUMBNAIL = 'thumbnail';
    const SIZE_FULL = 'full';
    const SIZE_SHARE_VIEW = 'share_view';
    const SIZE_REPORT = 'report';

    /**
     * @param Map $map
     * @param array $issues
     *
     * @return string
     */
    public function generateMapImage(Map $map, array $issues);

    /**
     * @param string|null $imagePath
     * @param string $size
     *
     * @return string|null
     */
    public function getSize(?string $imagePath, $size = self::SIZE_THUMBNAIL);
}
