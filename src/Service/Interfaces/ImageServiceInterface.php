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

    // where image is only asset
    // like map in report (A4 at 300 PPI)
    const SIZE_FULL = 'full';

    // all valid sizes
    const VALID_SIZES = [self::SIZE_THUMBNAIL, self::SIZE_PREVIEW, self::SIZE_FULL];

    public function resizeIssueImage(IssueImage $issueImage, string $size = self::SIZE_THUMBNAIL): ?string;

    public function resizeConstructionSiteImage(ConstructionSiteImage $constructionSiteImage, string $size = self::SIZE_THUMBNAIL): ?string;

    public function renderMapFileToJpg(MapFile $mapFile, string $size = self::SIZE_THUMBNAIL): ?string;

    /**
     * @param Issue[] $issues
     *
     * @return resource
     */
    public function renderMapFileWithIssues(MapFile $mapFile, array $issues, string $size = self::SIZE_THUMBNAIL);
}
