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

class Frame
{
    /**
     * @var float
     */
    public $startX;

    /**
     * @var float
     */
    public $startY;

    /**
     * @var float
     */
    public $width;

    /**
     * @var float
     */
    public $height;

    /**
     * @param Frame|array|\stdClass|null $source
     *
     * @return Frame|null
     */
    public static function createFromStdClass($source)
    {
        if (\is_array($source)) {
            if (\count($source) !== 4) {
                return null;
            }

            $source = (object) $source;
        }

        $frame = new self();
        $frame->height = $source->height;
        $frame->width = $source->width;
        $frame->startX = $source->startX;
        $frame->startY = $source->startY;

        return $frame;
    }

    /**
     * @param Frame|null $other
     *
     * @return bool
     */
    public function equals($other)
    {
        return $other !== null &&
            $this->startX === $other->startX &&
            $this->startY === $other->startY &&
            $this->width === $other->width &&
            $this->height === $other->height;
    }
}
