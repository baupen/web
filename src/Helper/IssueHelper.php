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

use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Map;
use function ksort;

class IssueHelper
{
    /**
     * @param Issue[]   $issues
     * @param Map[]     $orderedMaps
     * @param Issue[][] $issuesPerMap
     */
    public static function issuesToOrderedMaps(array $issues, &$orderedMaps, &$issuesPerMap)
    {
        $unorderedElements = [];
        /** @var Issue[][] $issuesPerElement */
        $issuesPerMap = [];
        foreach ($issues as $issue) {
            $map = $issue->getMap();
            $unorderedElements[$map->getNameWithContext()] = $map;
            $issuesPerMap[$map->getId()][] = $issue;
        }

        ksort($unorderedElements);

        $orderedMaps = [];
        foreach ($unorderedElements as $orderedElement) {
            $orderedMaps[$orderedElement->getId()] = $orderedElement;
        }
    }

    /**
     * @param Issue[]     $issues
     * @param Craftsman[] $orderedCraftsman
     * @param Issue[][]   $issuesPerCraftsman
     */
    public static function issuesToOrderedCraftsman(array $issues, &$orderedCraftsman, &$issuesPerCraftsman)
    {
        $unorderedElements = [];
        /** @var Issue[][] $issuesPerElement */
        $issuesPerCraftsman = [];
        foreach ($issues as $issue) {
            $craftsman = $issue->getCraftsman();
            $unorderedElements[$craftsman->getTrade().$craftsman->getCompany()] = $craftsman;
            $issuesPerCraftsman[$craftsman->getId()][] = $issue;
        }

        ksort($unorderedElements);

        $orderedCraftsman = [];
        foreach ($unorderedElements as $orderedElement) {
            $orderedCraftsman[$orderedElement->getId()] = $orderedElement;
        }
    }
}
