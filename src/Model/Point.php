<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

class Point
{
    /**
     * @var float
     */
    public $x;

    /**
     * @var float
     */
    public $y;

    /**
     * @param Point|array|\stdClass|null $source
     *
     * @return Point|null
     */
    public static function createFromStdClass($source)
    {
        if (\is_array($source)) {
            if (\count($source) !== 2) {
                return null;
            }

            $source = (object) $source;
        }

        $frame = new self();
        $frame->x = $source->x;
        $frame->y = $source->y;

        return $frame;
    }
}
