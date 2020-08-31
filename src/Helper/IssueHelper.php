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

class IssueHelper
{
    /**
     * @param Issue[] $issues
     * @param $orderedMaps
     * @param $issuesPerMap
     */
    public static function issuesToOrderedMaps(array $issues, &$orderedMaps, &$issuesPerMap)
    {
        static::issuesToOrderedElements(
            $issues,
            $orderedMaps,
            $issuesPerMap,
            function ($issue) {
                /* @var Issue $issue */
                return $issue->getMap();
            },
            function ($map) {
                /* @var Map $map */
                return $map->getName().$map->getContext();
            },
            function ($map) {
                /* @var Map $map */
                return $map->getId();
            }
        );
    }

    /**
     * @param Issue[] $issues
     * @param $orderedCraftsman
     * @param $issuesPerCraftsman
     */
    public static function issuesToOrderedCraftsman(array $issues, &$orderedCraftsman, &$issuesPerCraftsman)
    {
        static::issuesToOrderedElements(
            $issues,
            $orderedCraftsman,
            $issuesPerCraftsman,
            function ($issue) {
                /* @var Issue $issue */
                return $issue->getCraftsman();
            },
            function ($craftsman) {
                /* @var Craftsman $craftsman */
                return $craftsman->getName();
            },
            function ($craftsman) {
                /* @var Craftsman $craftsman */
                return $craftsman->getId();
            }
        );
    }

    /**
     * @param Issue[] $issues
     * @param $orderedTrade
     * @param $issuesPerTrade
     */
    public static function issuesToOrderedTrade(array $issues, &$orderedTrade, &$issuesPerTrade)
    {
        static::issuesToOrderedElements(
            $issues,
            $orderedTrade,
            $issuesPerTrade,
            function ($issue) {
                /* @var Issue $issue */
                return $issue->getCraftsman()->getTrade();
            },
            function ($trade) {
                /* @var string $trade */
                return $trade;
            },
            function ($trade) {
                /* @var string $trade */
                return $trade;
            }
        );
    }

    /**
     * @param $orderedElements
     * @param $issuesPerElement
     * @param callable $elementProperty    the element we want to use for ordering
     * @param callable $orderProperty      the property of the element we want to use for the order
     * @param callable $identifierProperty the unique identifier for the element we use for ordering
     */
    private static function issuesToOrderedElements(array $issues, &$orderedElements, &$issuesPerElement, $elementProperty, $orderProperty, $identifierProperty)
    {
        $unorderedElements = [];
        /** @var Issue[][] $issuesPerElement */
        $issuesPerElement = [];
        foreach ($issues as $issue) {
            $element = $elementProperty($issue);
            $unorderedElements[$orderProperty($element)] = $element;
            $issuesPerElement[$identifierProperty($element)][] = $issue;
        }

        ksort($unorderedElements);

        $orderedElements = [];
        foreach ($unorderedElements as $orderedElement) {
            $orderedElements[$identifierProperty($orderedElement)] = $orderedElement;
        }
    }
}
