<?php

namespace App\Service\Image;

use App\Entity\Issue;
use App\Service\Interfaces\PathServiceInterface;

class ContentDrawingService
{

    /**
     * @var GdService
     */
    private $gdService;

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
     * @return string[][]
     */
    public function getContent(array $issues): array
    {
        $content = [];
        foreach ($issues as $issue) {
            if ($issue->hasPosition()) {
                $circleColor = null !== $issue->getClosedAt() ? 'green' : 'orange';
                $issueText = (string)$issue->getNumber();
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
    public function processContentToDraw(string $sourcePath, string $targetPath, array $content)
    {
        $image = imagecreatefromjpeg($sourcePath);
        $xSize = imagesx($image);
        $ySize = imagesy($image);

        [$actualFontSize, $actualPadding, $averageContentHeight, $widthPadding] = $this->placeWithOptimalFontScale($content, $xSize, $ySize);
        $this->alignYIntoRows($content, $averageContentHeight);
        $content = $this->groupOverlaps($content, $widthPadding, $actualFontSize, $groups);

        foreach ($content as $entry) {
            $this->gdService->drawRectangleWithText($entry['xCoordinate'], $entry['yCoordinate'], $entry['color'], $actualPadding, $entry['text'], $actualFontSize, $entry['width'], $entry['height'], $image);
        }

        imagejpeg($image, $targetPath);
    }


    private function placeWithOptimalFontScale(array &$content, int $xSize, int $ySize)
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

        $averageContentHeight = $averageTextHeight * $fontScale + $padding / 2 + 2; // +2 for the border
        $widthPadding = $padding / 2 + 2;
        return [$actualFontSize, $actualPadding, $averageContentHeight, $widthPadding];
    }

    private function ensureMaxBounds(float $maxSize, float $currentSize, float $currentMultiplier)
    {
        $resultSize = $currentSize * $currentMultiplier;

        return $currentMultiplier * min($maxSize / $resultSize, 1);
    }

    private function alignYIntoRows(array &$content, float $averageContentHeight)
    {
        $offset = $averageContentHeight / 2;
        foreach ($content as &$entry) {
            $row = floor($entry['yCoordinate'] / $averageContentHeight);
            $entry['yCoordinate'] = $row * $averageContentHeight + $offset;
            $entry['row'] = $row;
        }
    }

    private function groupOverlaps(array $content, float $widthPadding, float $fontSize, array &$groups = null)
    {
        // sort by row and xCoordinate
        usort($content, function ($a, $b) {
            if ($a['row'] !== $b['row']) {
                return $a['row'] <=> $b['row'];
            }
            return $a['xCoordinate'] <=> $b['xCoordinate'];
        });

        $groups = [];
        $newContent = [];
        $lastRow = -1;
        $lastEnd = -1;
        $activeGroup = [];
        foreach ($content as $item) {
            $currentEnd = $item['xCoordinate'] + $item['width'] / 2 + $widthPadding;
            $currentStart = $item['xCoordinate'] - $item['width'] / 2 - $widthPadding;
            $currentRow = $item['row'];

            // if new row, reset state
            if ($lastRow !== $currentRow) {
                $lastRow = $currentRow;
                $lastEnd = $currentEnd;
                $newContent[] = $item;

                $activeGroup = [];
                continue;
            }

            // no overlap, all OK
            if ($lastEnd < $currentStart) {
                $lastEnd = $currentEnd;
                $newContent[] = $item;

                $activeGroup = [];
                continue;
            }

            // new overlap, merge with previous
            if (count($activeGroup) === 0) {
                $lastItem = array_pop($newContent);
                $groupName = $this->generateAlphabetColumnName(count($groups));
                $activeGroup = [$lastItem['text'], $item['text']];
                $groups[$groupName] = $activeGroup;

                list($textWidth) = $this->gdService->measureTextDimensions($fontSize, $groupName);
                $lastItem['text'] = $groupName;
                $lastItem['width'] = $textWidth;
                $lastItem['color'] = 'gray';
                $lastItem['xCoordinate'] = ($lastItem['xCoordinate'] + $item['xCoordinate']) / 2;
                $newContent[] = $lastItem;

                $lastEnd = $item['xCoordinate'] + $item['width'] / 2 + $widthPadding;
                continue;
            }

            // still within overlap, merge with previous
            if (count($activeGroup) > 0) {
                $activeGroup[] = $item['text'];

                // adjust x coordinate (weighted)
                $lastItem = array_pop($newContent);
                $lastItem['xCoordinate'] = ($lastItem['xCoordinate'] * (count($activeGroup) - 1) + $item['xCoordinate']) / count($activeGroup);
                $newContent[] = $lastItem;

                $lastEnd = $item['xCoordinate'] + $item['width'] / 2 + $widthPadding;
                continue;
            }
        }

        return $newContent;
    }

    private function generateAlphabetColumnName(int $index): string
    {
        $ordA = ord('A');
        $ordZ = ord('Z');
        $len = $ordZ - $ordA + 1;

        $result = '';
        while ($index >= 0) {
            $result = chr($index % $len + $ordA) . $result;
            $index = intval($index / $len) - 1;
        }

        return $result;
    }
}