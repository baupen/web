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

use App\Entity\Traits\IdTrait;

class HashHelper
{
    const HASH_LENGTH = 20;

    /**
     * generates a hash from alpha numeric characters of length 20.
     *
     * @return string
     */
    public static function getHash()
    {
        $newHash = '';
        //0-9, A-Z, a-z
        $allowedRanges = [[48, 57], [65, 90], [97, 122]];
        $rangeCount = \count($allowedRanges);
        for ($i = 0; $i < static::HASH_LENGTH; ++$i) {
            $rand = mt_rand(20, 160);
            $allowed = false;
            for ($j = 0; $j < $rangeCount; ++$j) {
                if ($allowedRanges[$j][0] <= $rand && $allowedRanges[$j][1] >= $rand) {
                    $allowed = true;
                }
            }
            if ($allowed) {
                $newHash .= \chr($rand);
            } else {
                --$i;
            }
        }

        return $newHash;
    }

    /**
     * creates a hash from the entities using the guid.
     *
     * @param $entities
     *
     * @return string
     */
    public static function hashEntities($entities)
    {
        return hash('sha256',
            implode(
                ',',
                array_map(
                    function ($issue) {
                        /* @var IdTrait $issue */
                        return $issue->getId();
                    },
                    $entities)
            )
        );
    }

    /**
     * creates a hash from a 2d arrays of entities using the guid.
     *
     * @param IdTrait[][] $entities
     *
     * @return string
     */
    public static function hash2dEntities($entities)
    {
        $res = [];
        foreach ($entities as $innerEntities) {
            $res[] = implode(
                ',',
                array_map(
                    function ($issue) {
                        /* @var IdTrait $issue */
                        return $issue->getId();
                    },
                    $innerEntities)
            );
        }

        return hash('sha256', implode(',', $res));
    }
}
