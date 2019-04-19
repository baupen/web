<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Sync;

use App\Service\Sync\Interfaces\DisplayNameServiceInterface;
use function array_key_exists;
use function count;

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
     * @param string $imageName
     *
     * @return string
     */
    public function forConstructionSiteImage(string $imageName)
    {
        $output = $this->trimEnding($imageName);
        $output = $this->trimWhitespace($output);

        return $output;
    }

    /**
     * @param string $mapName
     *
     * @return string
     */
    public function forMapFile(string $mapName)
    {
        $output = $this->trimEnding($mapName);

        // replace _ with space
        $output = str_replace('_', ' ', $output);

        // add points + space after all numbers immediately followed by text
        while (preg_match('/([0-9]){1,2}[A-Z]([a-z]){2,}/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $before = mb_substr($output, 0, $matches[0][1]);
            $number = $matches[1][0];
            $after = mb_substr($output, $matches[1][1] + mb_strlen($number));

            $output = $before . $number . '. ' . $after;
        }

        // add space before all capitals which are followed by at least 2 non-capital (ObergeschossHaus)
        $output = preg_replace('/(?<!^)([A-Z][a-z]{2,})/', ' $0', $output);

        // add space before all numbers (Haus2 -> Haus 2)
        // at most two following numbers
        $output .= ' ';
        while (preg_match('/([A-Za-z])+([0-9A-Z]){1,3} /', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $before = mb_substr($output, 0, $matches[1][1] + 1);
            $after = mb_substr($output, $matches[2][1]);

            $output = $before . ' ' . $after;
        }

        $output = $this->trimWhitespace($output);

        return $output;
    }

    /**
     * @param string[] $mapNames
     *
     * @return string[]
     */
    public function normalizeMapNames(array $mapNames)
    {
        //skip normalization if too few map names
        if (count($mapNames) < 3) {
            return $mapNames;
        }

        /** @var string[] $filenameGroupsStatistics */
        /** @var string[][] $decomposedMapNames */
        list($filenameGroupsStatistics, $decomposedMapNames) = $this->groupFilenameParts($mapNames);

        $this->removeIdenticalGroups($filenameGroupsStatistics, $decomposedMapNames);

        $this->removeDateGroups($decomposedMapNames);

        $counter = 0;
        $resultingNames = [];
        foreach ($mapNames as $key => $mapName) {
            // join parts back together
            $newName = implode(' ', $decomposedMapNames[$counter++]);

            $newName = $this->trimWhitespace($newName);

            $resultingNames[$key] = $newName;
        }

        return $resultingNames;
    }

    /**
     * @param string[] $elementNames as an (int id => string name) structure
     * @param callable $createNewElement called as $addElement(string $name); should return int id of the new element
     * @param callable $assignChildToParent called with $assignParent(string $childId, string $parentId)
     * @param callable $clearParent called with $clearParent(string $childId)
     */
    public function putIntoTreeStructure(array $elementNames, callable $createNewElement, callable $assignChildToParent, callable $clearParent)
    {
        // create dictionary for each name to point to the first element with that name
        $prefixElementIdMap = [];
        foreach ($elementNames as $id => $name) {
            if (!array_key_exists($name, $prefixElementIdMap)) {
                $prefixElementIdMap[$name] = $id;
            }
        }

        // ensure a map exists for all common prefixes (as a folder)
        $prefixCountMap = $this->createPrefixMap($elementNames);

        // ensure an element exists for all common prefixes
        foreach ($prefixCountMap as $prefix => $count) {
            if ($count > 1) {
                if (!array_key_exists($prefix, $prefixElementIdMap)) {
                    $newElementId = $createNewElement($prefix);
                    $elementNames[$newElementId] = $prefix;
                    $prefixElementIdMap[$prefix] = $newElementId;
                }
            }
        }

        // find longest matching prefix & set as parent
        foreach ($elementNames as $elementKey => $elementName) {
            $possibleParentPrefix = $elementName;

            $found = false;
            while (mb_strpos($possibleParentPrefix, ' ') !== false) {
                // cut off last part of name separated by space
                $possibleParentPrefix = mb_substr($possibleParentPrefix, 0, mb_strrpos($possibleParentPrefix, ' '));

                // assign to parent if found
                if (array_key_exists($possibleParentPrefix, $prefixElementIdMap)) {
                    $assignChildToParent($elementKey, $prefixElementIdMap[$possibleParentPrefix]);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $clearParent($elementKey);
            }
        }
    }

    /**
     * counts prefixes occurring and returns a dictionary with (prefix => count).
     *
     * @param string[] $names
     *
     * @return int[]
     */
    private function createPrefixMap(array $names)
    {
        $prefixMap = [];

        foreach ($names as $name) {
            $currentPrefix = $name;

            while (true) {
                if (!array_key_exists($currentPrefix, $prefixMap)) {
                    $prefixMap[$currentPrefix] = 1;
                } else {
                    ++$prefixMap[$currentPrefix];
                }

                $newCutoff = mb_strripos($currentPrefix, ' ');
                if ($newCutoff === false) {
                    break;
                }

                $currentPrefix = trim(mb_substr($currentPrefix, 0, $newCutoff));
            }
        }

        return $prefixMap;
    }

    /**
     * @param array $names
     *
     * @return string[],\string[][]
     */
    private function groupFilenameParts(array $names)
    {
        // remove any entries occurring always
        /** @var string[][] $filenameGroupCount */
        $filenameGroupCount = [];
        $decomposedNames = [];

        // collect stats about file names
        foreach ($names as $name) {
            $parts = explode(' ', $name);
            $decomposedNames[] = $parts;
            $partCount = count($parts);
            for ($i = 0; $i < $partCount; ++$i) {
                if (!array_key_exists($i, $filenameGroupCount)) {
                    $filenameGroupCount[$i] = [];
                }

                $currentPart = $parts[$i];
                if (!array_key_exists($currentPart, $filenameGroupCount[$i])) {
                    $filenameGroupCount[$i][$currentPart] = 1;
                } else {
                    ++$filenameGroupCount[$i][$currentPart];
                }
            }
        }

        return [$filenameGroupCount, $decomposedNames];
    }

    /**
     * @param string[] $filenameGroupsStatistics
     * @param string[] $decomposedNames
     */
    private function removeIdenticalGroups(array &$filenameGroupsStatistics, array &$decomposedNames)
    {
        // remove groups which are always the same
        $partAnalyticsCount = count($filenameGroupsStatistics);
        for ($i = 0; $i < $partAnalyticsCount; ++$i) {
            // only one value; can safely remove because will not contain any useful information
            if (count($filenameGroupsStatistics[$i]) === 1) {
                $this->removeGroup($i, $filenameGroupsStatistics, $decomposedNames);

                --$partAnalyticsCount;
                --$i;
            }
        }
    }

    /**
     * @param string[] $decomposedNames
     */
    private function removeDateGroups(array &$decomposedNames)
    {
        foreach ($decomposedNames as &$decomposedName) {
            $lastIndex = count($decomposedName) - 1;
            $possibleDateGroup = $decomposedName[$lastIndex];

            if (is_numeric($possibleDateGroup)) {
                if (mb_strlen($possibleDateGroup) === 6) {
                    $year = '20' . mb_substr($possibleDateGroup, 0, 2);
                    $month = mb_substr($possibleDateGroup, 2, 2);
                    $day = mb_substr($possibleDateGroup, 4, 2);

                    if (checkdate($month, $day, $year)) {
                        unset($decomposedName[$lastIndex]);
                    }
                } elseif (mb_strlen($possibleDateGroup) === 8) {
                    $year = mb_substr($possibleDateGroup, 0, 4);
                    $month = mb_substr($possibleDateGroup, 4, 2);
                    $day = mb_substr($possibleDateGroup, 6, 2);

                    if (checkdate($month, $day, $year)) {
                        unset($decomposedName[$lastIndex]);
                    }
                }
            }
        }
    }

    /**
     * @param int $index
     * @param string[] $filenameGroupsStatistics
     * @param string[] $decomposedNames
     */
    private function removeGroup(int $index, array &$filenameGroupsStatistics, array &$decomposedNames)
    {
        // remove from parts list
        foreach ($decomposedNames as &$name) {
            unset($name[$index]);
            $name = array_values($name);
        }

        //remove processed entry group
        unset($filenameGroupsStatistics[$index]);
        $filenameGroupsStatistics = array_values($filenameGroupsStatistics);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function trimEnding(string $fileName)
    {
        // strip file ending
        $ending = pathinfo($fileName, PATHINFO_EXTENSION);

        // strip duplicate
        if (preg_match('/_duplicate_[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}_[0-9]{2}.' . $ending . '/', $fileName, $matches, PREG_OFFSET_CAPTURE)) {
            $index = $matches[0][1];
            $output = mb_substr($fileName, 0, $index);
        } else {
            $output = pathinfo($fileName, PATHINFO_FILENAME);
        }

        return $output;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function trimWhitespace(string $text)
    {
        // remove multiple whitespaces
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
