<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueEventFile;
use App\Entity\IssueImage;
use App\Entity\MapFile;

interface ImageServiceInterface
{
    // to show in list; small but able to see contours
    public const SIZE_THUMBNAIL = 'thumbnail';

    // show in list where image is primary asset
    // 4 side by side on A4 at 300 PPI
    public const SIZE_PREVIEW = 'preview';

    // where image is only asset
    // like map in report (A4 at 300 PPI)
    public const SIZE_FULL = 'full';

    // all valid sizes
    public const VALID_SIZES = [self::SIZE_THUMBNAIL, self::SIZE_PREVIEW, self::SIZE_FULL];

    public const IMAGE_FILENAME_ENDINGS = ['jpg', 'jpeg', 'png', 'gif'];

    public function isImageFilename(string $filename): bool;

    public function resizeIssueImage(IssueImage $issueImage, string $size = self::SIZE_THUMBNAIL): ?string;

    public function resizeConstructionSiteImage(ConstructionSiteImage $constructionSiteImage, string $size = self::SIZE_THUMBNAIL): ?string;

    public function resizeIssueEventImage(IssueEventFile $issueEventFile, string $size = self::SIZE_THUMBNAIL): ?string;

    public function renderMapFileToJpg(MapFile $mapFile, string $size = self::SIZE_THUMBNAIL): ?string;

    /**
     * @param Issue[] $issues
     */
    public function renderMapFileWithIssuesToJpg(MapFile $mapFile, array $issues, string $size = self::SIZE_THUMBNAIL): ?string;

    public function renderMapFileWithSingleIssueToJpg(MapFile $mapFile, Issue $issue, string $size = self::SIZE_THUMBNAIL): ?string;
}
