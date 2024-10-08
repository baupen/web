<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Helper;

class HashHelper
{
    public const HASH_LENGTH = 20;

    /**
     * generates a hash from alpha numeric characters of length 20.
     */
    public static function getHash(): string
    {
        $newHash = '';
        // 0-9, A-Z, a-z
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
}
