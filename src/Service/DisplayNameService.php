<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Service\Interfaces\DisplayNameServiceInterface;

class DisplayNameService implements DisplayNameServiceInterface
{
    /**
     * @param string $folderName
     *
     * @return string
     */
    public function forConstructionSite(string $folderName)
    {
        return str_replace('_', ' ', $folderName);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function trimFilename(string $fileName)
    {
        // strip file ending
        $ending = pathinfo($fileName, PATHINFO_EXTENSION);

        // strip artificial hash
        if (preg_match('/_hash([A-Fa-f0-9]){64}.' . $ending . '/', $fileName, $matches, PREG_OFFSET_CAPTURE)) {
            $index = $matches[0][1];
            $output = mb_substr($fileName, 0, $index);
        } else {
            $output = pathinfo($fileName, PATHINFO_FILENAME);
        }

        return $output;
    }

    /**
     * @param string $imageName
     *
     * @return string
     */
    public function forConstructionSiteImage(string $imageName)
    {
        $output = $this->trimFilename($imageName);

        return $output;
    }

    /**
     * @param string $mapName
     *
     * @return string
     */
    public function forMapFile(string $mapName)
    {
        $output = $this->trimFilename($mapName);

        // replace _ with space
        $output = str_replace('_', ' ', $output);

        // add space before all capitals which are followed by at least 2 non-capital (ObergeschossHaus)
        $output = preg_replace('/(?<!^)([A-Z][a-z]{2,})/', ' $0', $output);

        // add space before all numbers (Haus2)
        $output = preg_replace('/(?<!^)([0-9]+)/', ' $0', $output);

        // add point after all numbers which are before any letters
        if (preg_match('/[a-zA-Z]/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $index = $matches[0][1];
            $before = mb_substr($output, 0, $index);
            $after = mb_substr($output, $index);

            // match single numbers followed by a space (1 Obergeschoss) to add a point
            while (preg_match('/[0-9]{1}[ ]/', $before, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches as $match) {
                    $matchLength = mb_strlen($match[0]);
                    $index = $match[1];
                    $before = mb_substr($before, 0, $index - 1) . '. ' . mb_substr($before, $index + $matchLength);
                }
            }

            $output = $before . $after;
        }

        // remove multiple whitespaces
        return preg_replace('/\s+/', ' ', $output);
    }

    /**
     * @param string[] $mapNames
     *
     * @return string[]
     */
    public function normalizeMapNames(array $mapNames)
    {
        //skip normalization if too few map names
        if (\count($mapNames) < 3) {
            return $mapNames;
        }

        // remove any entries occurring always
        /** @var string[][] $partsAnalytics */
        $partsAnalytics = [];
        $mapParts = [];

        // collect stats about file names
        foreach ($mapNames as $mapName) {
            $parts = explode(' ', $mapName);
            $mapParts[] = $parts;
            for ($i = 0; $i < \count($parts); ++$i) {
                if (!array_key_exists($i, $partsAnalytics)) {
                    $partsAnalytics[$i] = [];
                }

                $currentPart = $parts[$i];
                if (!array_key_exists($currentPart, $partsAnalytics[$i])) {
                    $partsAnalytics[$i][$currentPart] = 1;
                } else {
                    ++$partsAnalytics[$i][$currentPart];
                }
            }
        }

        // remove groups which are always the same
        for ($i = 0; $i < \count($partsAnalytics); ++$i) {
            // only one value; can safely remove because will not contain any useful information
            if (\count($partsAnalytics[$i]) === 1) {
                // remove from parts list
                foreach ($mapParts as &$mapPart) {
                    unset($mapPart[$i]);
                    $mapPart = array_values($mapPart);
                }

                //remove processed entry group
                unset($partsAnalytics[$i]);
                $partsAnalytics = array_values($partsAnalytics);
                --$i;
            }
        }

        // remove groups which are very likely date groups
        for ($i = 0; $i < \count($partsAnalytics); ++$i) {
            $probablyDateGroup = true;
            foreach ($partsAnalytics[$i] as $element => $counter) {
                if (!is_numeric($element)) {
                    $probablyDateGroup = false;
                    break;
                }

                $probableYear = mb_substr($element, 0, 2);
                $currentYear = mb_substr(date('Y'), 2, 2);
                if ($probableYear < 10 || $probableYear > $currentYear) {
                    $probablyDateGroup = false;
                    break;
                }
            }

            if ($probablyDateGroup) {
                // remove from parts list
                foreach ($mapParts as &$mapPart) {
                    unset($mapPart[$i]);
                    $mapPart = array_values($mapPart);
                }

                //remove processed entry group
                unset($partsAnalytics[$i]);
                $partsAnalytics = array_values($partsAnalytics);
                --$i;
            }
        }

        $counter = 0;
        $resultingNames = [];
        foreach ($mapNames as $key => $mapName) {
            // join parts back together
            $newName = implode(' ', $mapParts[$counter++]);

            // remove multiple whitespaces
            $newName = preg_replace('/\s+/', ' ', $newName);

            $resultingNames[$key] = $newName;
        }

        return $resultingNames;
    }
}
