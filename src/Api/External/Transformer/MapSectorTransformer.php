<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Transformer;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\MapSector;

class MapSectorTransformer extends BatchTransformer
{
    /**
     * @var PointTransformer
     */
    private $pointTransformer;

    /**
     * MapSectorTransformer constructor.
     *
     * @param PointTransformer $pointTransformer
     */
    public function __construct(PointTransformer $pointTransformer)
    {
        $this->pointTransformer = $pointTransformer;
    }

    /**
     * @param MapSector $entity
     *
     * @return \App\Api\External\Entity\MapSector
     */
    public function toApi($entity)
    {
        $mapSector = new \App\Api\External\Entity\MapSector();
        $mapSector->setName($entity->getName());
        $mapSector->setColor($entity->getColor());
        $mapSector->setPoints($this->pointTransformer->toApiMultiple($entity->getPoints()));

        return $mapSector;
    }
}
