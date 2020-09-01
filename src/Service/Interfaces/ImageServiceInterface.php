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

use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\MapFile;

interface ImageServiceInterface
{
    // to show in list; small but able to see contours
    const SIZE_THUMBNAIL = 'thumbnail';

    // show in list where image is primary asset
    // 4 side by side on A4 at 300 PPI
    const SIZE_PREVIEW = 'preview';

    // size of map in report
    // A4 at 300 PPI
    const SIZE_REPORT_MAP = 'report_map';

    /**
     * @return bool
     */
    public function isValidSize(string $uncheckedSize);

    public function resizeIssueImage(IssueImage $issueImage, string $size = self::SIZE_THUMBNAIL): ?string;

    public function resizeConstructionSiteImage(ConstructionSiteImage $constructionSiteImage, string $size = self::SIZE_THUMBNAIL): ?string;

    /**
     * @param Issue[] $issues
     */
    public function renderMapFileWithIssues(MapFile $mapFile, array $issues, string $size = self::SIZE_THUMBNAIL): ?string;

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForIssueImage(IssueImage $issueImage);

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForConstructionSiteImage(ConstructionSiteImage $constructionSiteImage);

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForMapFile(MapFile $mapFile);
}
