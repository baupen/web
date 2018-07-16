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
    const SIZE_REPORT_ISSUE = 'report_issue';
    const SIZE_REPORT_MAP = 'report_map';

    /**
     * @param Map $map
     * @param array $issues
     *
     * @return string
     */
    public function generateMapImage(Map $map, array $issues);

    /**
     * @param Map $map
     * @param array $issues
     *
     * @return string
     */
    public function generateMapImageForReport(Map $map, array $issues);

    /**
     * @param string|null $imagePath
     * @param string $size
     *
     * @return string|null
     */
    public function getSize(?string $imagePath, $size = self::SIZE_THUMBNAIL);

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     *
     * @param null|string $imagePath
     *
     * @return
     */
    public function warmupCache(?string $imagePath);
}
