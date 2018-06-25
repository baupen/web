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

class HashHelper
{
    const HASH_LENGTH = 20;

    /**
     * generates a hash from alpha nummeric characters of length 20.
     *
     * @return string
     */
    public static function getHash()
    {
        $newHash = '';
        //0-9, A-Z, a-z
        $allowedRanges = [[48, 57], [65, 90], [97, 122]];
        for ($i = 0; $i < static::HASH_LENGTH; ++$i) {
            $rand = mt_rand(20, 160);
            $allowed = false;
            for ($j = 0; $j < count($allowedRanges); ++$j) {
                if ($allowedRanges[$j][0] <= $rand && $allowedRanges[$j][1] >= $rand) {
                    $allowed = true;
                }
            }
            if ($allowed) {
                $newHash .= chr($rand);
            } else {
                --$i;
            }
        }

        return $newHash;
    }
}
