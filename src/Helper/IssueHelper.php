<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Helper;

use App\Entity\Issue;
use App\Entity\Map;

class IssueHelper
{
    /**
     * @param Issue[] $issues
     * @param $orderedMaps
     * @param $issuesPerMap
     */
    public static function issuesToOrderedMaps(array $issues, &$orderedMaps, &$issuesPerMap)
    {
        /** @var Map[] $unorderedMaps */
        $unorderedMaps = [];
        /** @var Issue[][] $issuesPerMap */
        $issuesPerMap = [];
        foreach ($issues as $issue) {
            $map = $issue->getMap();
            $unorderedMaps[$map->getName() . $map->getContext()] = $map;
            $issuesPerMap[$map->getId()][] = $issue;
        }

        ksort($unorderedMaps);

        /** @var Map[] $maps */
        $orderedMaps = [];
        foreach ($unorderedMaps as $orderedMap) {
            $orderedMaps[$orderedMap->getId()] = $orderedMap;
        }
    }
}
