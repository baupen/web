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
            if (preg_match('/[0-9]{1}[ ]/', $before, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches as $match) {
                    $matchLength = mb_strlen($match[0]);
                    $index = $match[1];
                    $before = mb_substr($before, 0, $index - 1) . '. ' . mb_substr($before, $index + $matchLength);
                }
            }

            $output = $before . $after;
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
        if (\count($mapNames) < 3) {
            return $mapNames;
        }

        /** @var string[][] $filenameGroupsStatistics */
        /** @var string[][] $decomposedMapNames */
        list($filenameGroupsStatistics, $decomposedMapNames) = $this->groupFilenameParts($mapNames);

        $this->removeIdenticalGroups($filenameGroupsStatistics, $decomposedMapNames);

        $this->removeDateGroups($filenameGroupsStatistics, $decomposedMapNames);

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
     */
    public function putIntoTreeStructure(array $elementNames, callable $createNewElement, callable $assignChildToParent)
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
            while (mb_strpos($possibleParentPrefix, ' ') !== false) {
                // cut off last part of name separated by space
                $possibleParentPrefix = mb_substr($possibleParentPrefix, 0, mb_strrpos($possibleParentPrefix, ' '));

                // assign to parent if found
                if (array_key_exists($possibleParentPrefix, $prefixElementIdMap)) {
                    $assignChildToParent($elementKey, $prefixElementIdMap[$possibleParentPrefix]);
                    break;
                }
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

                $newCutoff = \mb_strripos($currentPrefix, ' ');
                if ($newCutoff === false) {
                    break;
                }

                $currentPrefix = trim(\mb_substr($currentPrefix, 0, $newCutoff));
            }
        }

        return $prefixMap;
    }

    /**
     * @param array $names
     *
     * @return \string[][],\string[][]
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
            $partCount = \count($parts);
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
     * @param string[][] $filenameGroupsStatistics
     * @param string[][] $decomposedNames
     */
    private function removeIdenticalGroups(array &$filenameGroupsStatistics, array &$decomposedNames)
    {
        // remove groups which are always the same
        $partAnalyticsCount = \count($filenameGroupsStatistics);
        for ($i = 0; $i < $partAnalyticsCount; ++$i) {
            // only one value; can safely remove because will not contain any useful information
            if (\count($filenameGroupsStatistics[$i]) === 1) {
                $this->removeGroup($i, $filenameGroupsStatistics, $decomposedNames);

                --$partAnalyticsCount;
                --$i;
            }
        }
    }

    /**
     * @param string[][] $filenameGroupsStatistics
     * @param string[][] $decomposedNames
     */
    private function removeDateGroups(array &$filenameGroupsStatistics, array &$decomposedNames)
    {
        // remove groups which are always the same
        $partAnalyticsCount = \count($filenameGroupsStatistics);

        // remove groups which are very likely date groups
        for ($i = 0; $i < $partAnalyticsCount; ++$i) {
            $probablyDateGroup = true;

            // ensure all values are of the form 170816
            foreach ($filenameGroupsStatistics[$i] as $element => $counter) {
                if (!is_numeric($element)) {
                    $probablyDateGroup = false;
                    break;
                }

                // check that year is probable
                $probableYear = mb_substr($element, 0, 2);
                $currentYear = mb_substr(date('Y'), 2, 2);
                if ($probableYear < 10 || $probableYear > $currentYear) {
                    $probablyDateGroup = false;
                    break;
                }
            }

            // remove if all values matched
            if ($probablyDateGroup) {
                $this->removeGroup($i, $filenameGroupsStatistics, $decomposedNames);

                --$partAnalyticsCount;
                --$i;
            }
        }
    }

    /**
     * @param int $index
     * @param string[][] $filenameGroupsStatistics
     * @param string[][] $decomposedNames
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
        return preg_replace('/\s+/', ' ', $text);
    }
}
