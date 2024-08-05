<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Image;

use App\Entity\Issue;

class ContentDrawingService
{
    private GdService $gdService;

    /**
     * ImageService constructor.
     */
    public function __construct(GdService $gdService)
    {
        $this->gdService = $gdService;
    }

    /**
     * constants for drawing the issues on the maps.
     */
    private const PADDING_PROPORTION = 0.375; // how much padding proportional to height is drawn around the issue number
    private const SINGLE_CHARACTER_SURFACE_PERCENTAGE = 0.005; // how much surface of the image a single character should take
    private const MAX_CHARACTERS_SURFACE_PERCENTAGE = 0.1; // how much surface of the image all characters should take
    private const MAXIMAL_CHARACTER_PROPORTION = 0.05; // how large a single character is allowed to be relative to the full image
    private const MINIMAL_CHARACTER_HEIGHT = 8.0; // minimal pixels a single character is allowed to be
    private const MAXIMAL_CHARACTER_HEIGHT = 30.0; // maximal pixels a single character is allowed to be

    /**
     * @param Issue[] $issues
     *
     * @return string[][]
     */
    public function getContent(array $issues): array
    {
        $content = [];
        foreach ($issues as $issue) {
            if ($issue->hasPosition()) {
                $circleColor = null !== $issue->getClosedAt() ? 'green' : 'orange';
                $issueText = (string) $issue->getNumber();
                $content[] = [
                    'text' => $issueText,
                    'x' => $issue->getPositionX(),
                    'y' => $issue->getPositionY(),
                    'color' => $circleColor,
                ];
            }
        }

        return $content;
    }

    /**
     * @param string[][] $content
     */
    public function processContentToDraw(string $sourcePath, string $targetPath, array $content): void
    {
        $image = imagecreatefromjpeg($sourcePath);
        $xSize = imagesx($image);
        $ySize = imagesy($image);

        [$actualFontSize, $actualPadding, $averageContentHeight] = $this->placeWithOptimalFontScale($content, $xSize, $ySize);
        $this->alignYIntoRows($content, $actualPadding, $averageContentHeight);
        $content = $this->groupOverlaps($content, $actualPadding, $actualFontSize, $groups);

        foreach ($content as $entry) {
            $this->gdService->drawBorderedRectangleWithTextCentered($entry['xCoordinate'], $entry['yCoordinate'], $entry['color'], $actualPadding, $entry['text'], $actualFontSize, $entry['width'], $entry['height'], $image);
        }

        // remove padding whitespace that sometimes exist
        $image = imagecropauto($image, IMG_CROP_WHITE);
        $xSize = imagesx($image);
        $ySize = imagesy($image);

        if (count($groups) > 0) {
            $lineHeight = $averageContentHeight + 2 * $actualPadding + 2 + 2 * $actualPadding;
            $sectionPadding = 2 * $actualPadding;
            $requiredLines = $this->printGroups($image, $ySize + $sectionPadding, $lineHeight, $xSize, $actualPadding, $actualFontSize, $groups, false);
            $paddingBottom = $sectionPadding + $requiredLines * $lineHeight;

            // create image with white padding at the bottom
            $originalImage = $image;
            $image = imagecreatetruecolor($xSize, $ySize + $paddingBottom);
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefilledrectangle($image, 0, $ySize, $xSize, $ySize + $paddingBottom, $white);
            imagecopy($image, $originalImage, 0, 0, 0, 0, $xSize, $ySize);

            $this->printGroups($image, $ySize + $sectionPadding, $lineHeight, $xSize, $actualPadding, $actualFontSize, $groups, true);
        }

        imagejpeg($image, $targetPath);
    }

    private function printGroups(\GdImage $image, float $startY, float $lineHeight, int $availableXSpace, float $padding, float $fontSize, array $groups, bool $print): int
    {
        $startX = $padding * 2;

        $currentX = $startX;
        $currentLine = 0;

        $badgePadding = $padding * 2 + 2 + $padding;

        foreach ($groups as $groupEntries) {
            $contentWidth = 0;
            foreach ($groupEntries as $groupEntry) {
                $contentWidth += $groupEntry['width'] + $badgePadding;
            }
            $contentWidth += $padding * 2;

            if ($contentWidth + $currentX > $availableXSpace && $currentX > $startX) {
                ++$currentLine;
                $currentX = $startX;
            }

            if ($print) {
                $firstEntry = array_shift($groupEntries);
                usort($groupEntries, function (array $a, array $b): int {
                    return $a['text'] <=> $b['text'];
                });
                foreach ([$firstEntry, ...$groupEntries] as $groupEntry) {
                    $currentY = $startY + $currentLine * $lineHeight;
                    $this->gdService->drawRectangleWithText($currentX, $currentY, $groupEntry['color'], $padding, $groupEntry['text'], $fontSize, $groupEntry['width'], $groupEntry['height'], $image);
                    $currentX += $groupEntry['width'] + $badgePadding;
                }
                $currentX += $padding * 2;
            } else {
                $currentX += $contentWidth;
            }
        }

        // return total lines
        return $currentLine + 1;
    }

    private function placeWithOptimalFontScale(array &$content, int $xSize, int $ySize): array
    {
        // estimate how much is drawn on the map
        $measurementFontSize = 30;
        $totalTextWidth = 0;
        $totalTextLength = 0;
        $totalTextHeight = 0;
        foreach ($content as &$entry) {
            list($textWidth, $textHeight) = $this->gdService->measureTextDimensions($measurementFontSize, $entry['text']);

            $entry['width'] = $textWidth;
            $entry['height'] = $textHeight;

            $totalTextWidth += $textWidth;
            $totalTextLength += mb_strlen($entry['text']);
            $totalTextHeight += $textHeight;
        }
        unset($entry);

        $averageTextHeight = $totalTextHeight / count($content);
        $averageTextWidth = $totalTextWidth / $totalTextLength;
        $padding = $averageTextHeight * self::PADDING_PROPORTION;

        $imageSurface = $xSize * $ySize;
        $textSurface = ($totalTextWidth + 2 * count($content) * $padding) * ($averageTextHeight + 2 * $padding);

        $targetTextSurfaceShare = min(self::MAX_CHARACTERS_SURFACE_PERCENTAGE, self::SINGLE_CHARACTER_SURFACE_PERCENTAGE * $totalTextLength); // 0.5% for single issue; cap at 10%
        $actualTextSurfaceShare = $textSurface / $imageSurface;

        $optimalFontScale = sqrt($targetTextSurfaceShare / $actualTextSurfaceShare);
        // max: single character should not be larger than maxbounds of longer side
        if ($xSize < $ySize) {
            $fontScale = $this->ensureMaxBounds(self::MAXIMAL_CHARACTER_PROPORTION * $ySize, $averageTextHeight, $optimalFontScale);
        } else {
            $fontScale = $this->ensureMaxBounds(self::MAXIMAL_CHARACTER_PROPORTION * $xSize, $averageTextWidth, $optimalFontScale);
        }

        // min: single character should not be smaller
        $minFontScale = self::MINIMAL_CHARACTER_HEIGHT / $averageTextHeight;
        $fontScale = max($minFontScale, $fontScale);

        // max: single character should not be larger
        $maxFontScale = self::MAXIMAL_CHARACTER_HEIGHT / $averageTextHeight;
        $fontScale = min($maxFontScale, $fontScale);

        $actualPadding = $padding * $fontScale;
        $actualFontSize = $measurementFontSize * $fontScale;

        foreach ($content as &$entry) {
            $entry['xCoordinate'] = $entry['x'] * $xSize;
            $entry['yCoordinate'] = $entry['y'] * $ySize;
            $entry['width'] *= $fontScale;
            $entry['height'] *= $fontScale;
        }
        unset($entry);

        $averageContentHeight = $averageTextHeight * $fontScale;

        return [$actualFontSize, $actualPadding, $averageContentHeight];
    }

    private function ensureMaxBounds(float $maxSize, float $currentSize, float $currentMultiplier): float
    {
        $resultSize = $currentSize * $currentMultiplier;

        return $currentMultiplier * min($maxSize / $resultSize, 1);
    }

    private function alignYIntoRows(array &$content, float $padding, float $averageContentHeight): void
    {
        $rowHeight = $averageContentHeight + $padding + 2; // + 2 for border; give some slight padding
        $offset = $rowHeight / 2;
        foreach ($content as &$entry) {
            $row = floor($entry['yCoordinate'] / $rowHeight);
            $entry['yCoordinate'] = $row * $rowHeight + $offset;
            $entry['row'] = $row;
        }
    }

    /**
     * @return mixed[]
     */
    private function groupOverlaps(array $content, float $padding, float $fontSize, ?array &$groups = null): array
    {
        // sort by row and xCoordinate
        usort($content, function (array $a, array $b): int {
            if ($a['row'] !== $b['row']) {
                return $a['row'] <=> $b['row'];
            }

            return $a['xCoordinate'] <=> $b['xCoordinate'];
        });

        $paddingOverlap = $padding / 2 + 2;

        $groups = [];
        $newContent = [];
        $lastRow = -1;
        $lastEnd = -1;
        $activeGroup = [];
        foreach ($content as $item) {
            $currentEnd = $item['xCoordinate'] + $item['width'] / 2 + $paddingOverlap;
            $currentStart = $item['xCoordinate'] - $item['width'] / 2 - $paddingOverlap;
            $currentRow = $item['row'];

            // if new row, reset state
            if ($lastRow !== $currentRow) {
                $lastRow = $currentRow;
                $lastEnd = $currentEnd;
                $newContent[] = $item;

                if ([] !== $activeGroup) {
                    $groups[] = $activeGroup;
                    $activeGroup = [];
                }
                continue;
            }

            // no overlap, all OK
            if ($lastEnd < $currentStart) {
                $lastEnd = $currentEnd;
                $newContent[] = $item;

                if ([] !== $activeGroup) {
                    $groups[] = $activeGroup;
                    $activeGroup = [];
                }
                continue;
            }

            // new overlap, merge with previous
            if ([] === $activeGroup) {
                $groupName = $this->generateAlphabetColumnName(count($groups));
                list($textWidth, $textHeight) = $this->gdService->measureTextDimensions($fontSize, $groupName);

                $lastItem = array_pop($newContent);
                $groupItem['text'] = $groupName;
                $groupItem['width'] = $textWidth;
                $groupItem['height'] = $textHeight;
                $groupItem['color'] = 'gray';
                $groupItem['xCoordinate'] = ($lastItem['xCoordinate'] + $item['xCoordinate']) / 2;
                $groupItem['yCoordinate'] = $lastItem['yCoordinate'];
                $newContent[] = $groupItem;

                $activeGroup = [$groupItem, $lastItem, $item];

                $lastEnd = $item['xCoordinate'] + $item['width'] / 2 + $paddingOverlap;
                continue;
            }
            // still within overlap, merge with previous
            $activeGroup[] = $item;
            // adjust x coordinate (weighted)
            $lastItem = array_pop($newContent);
            $groupedItemsCount = count($activeGroup) - 1;
            // -1 as the first entry is the group header
            $lastItem['xCoordinate'] = ($lastItem['xCoordinate'] * ($groupedItemsCount - 1) + $item['xCoordinate']) / $groupedItemsCount;
            $newContent[] = $lastItem;
            $lastEnd = $item['xCoordinate'] + $item['width'] / 2 + $paddingOverlap;
        }

        if ([] !== $activeGroup) {
            $groups[] = $activeGroup;
        }

        return $newContent;
    }

    private function generateAlphabetColumnName(int $index): string
    {
        // exclude J, Q (rendering messes up the baseline) and I (as could be confused with 1)
        $values = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $len = count($values);

        $result = '';
        while ($index >= 0) {
            $result = $values[$index % $len].$result;
            $index = intval($index / $len) - 1;
        }

        return $result;
    }
}
