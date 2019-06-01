<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Edit;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\MapSector;
use Exception;

class MapSectorTransformer extends BatchTransformer
{
    /**
     * @param MapSector $entity
     *
     * @throws Exception
     *
     * @return \App\Api\Entity\Edit\MapSector
     */
    public function toApi($entity)
    {
        $mapFile = new \App\Api\Entity\Edit\MapSector();
        $mapFile->setName($entity->getName());
        $mapFile->setColor($entity->getColor());
        $mapFile->setPoints($entity->getPoints());

        return $mapFile;
    }
}
