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

class RandomHelper
{
    /**
     * @return bool|string
     */
    public static function generateHumanReadableRandom(int $minimalLength, string $divider)
    {
        $vocals = 'aeiou';
        $vocalsLength = mb_strlen($vocals);

        //skip because ambiguous: ck, jyi
        $normals = 'bdfghklmnpqrstvwxz';
        $normalsLength = mb_strlen($normals);

        $randomString = '';
        $length = 0;
        do {
            if ($length > 0) {
                $randomString .= $divider;
                ++$length;
            }

            // create bigger group
            $randomString .= self::getRandomChar($normals, $normalsLength);
            $randomString .= self::getRandomChar($vocals, $vocalsLength);
            $randomString .= self::getRandomChar($normals, $normalsLength);
            $length += 3;

            // abort if too big already
            if ($length > $minimalLength) {
                break;
            }

            // create smaller group
            $randomString .= $divider;
            $randomString .= self::getRandomChar($normals, $normalsLength);
            $randomString .= self::getRandomChar($vocals, $vocalsLength);
            $length += 3;
        } while ($length < $minimalLength);

        return $randomString;
    }

    /**
     * @return bool|string
     */
    private static function getRandomChar(string $selection, int $selectionLength)
    {
        $entry = rand(0, $selectionLength - 1);

        return mb_substr($selection, $entry, 1);
    }
}
