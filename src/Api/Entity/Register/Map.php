<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Register;

class Map extends \App\Api\Entity\Base\Map
{
    /**
     * @var Map[]
     */
    private $children;

    /**
     * @return Map[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param Map[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }
}
