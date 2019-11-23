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

use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\Map;

interface ImageServiceInterface
{
    const SIZE_THUMBNAIL = 'thumbnail';
    const SIZE_FULL = 'full';
    const SIZE_SHARE_VIEW = 'share_view';
    const SIZE_REPORT_ISSUE = 'report_issue';
    const SIZE_MEDIUM = 'medium';
    const SIZE_REPORT_MAP = 'report_map';

    /**
     * @param string $uncheckedSize
     *
     * @return string
     */
    public function ensureValidSize($uncheckedSize);

    /**
     * @param string $size
     *
     * @return string|null
     */
    public function generateMapImage(Map $map, array $issues, $size = self::SIZE_THUMBNAIL);

    /**
     * @param string $size
     *
     * @return string|null
     */
    public function generateMapImageForReport(Map $map, array $issues, $size = self::SIZE_THUMBNAIL);

    /**
     * @param string $size
     *
     * @return string|null
     */
    public function getSizeForIssue(Issue $issue, $size = self::SIZE_THUMBNAIL);

    /**
     * @param string $size
     *
     * @return string|null
     */
    public function getSizeForConstructionSite(ConstructionSite $constructionSite, $size = self::SIZE_THUMBNAIL);

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForIssue(Issue $issue);

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForConstructionSite(ConstructionSite $constructionSite);

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForMap(Map $map);
}
