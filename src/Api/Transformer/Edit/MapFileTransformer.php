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
use App\Entity\MapFile;
use Exception;

class MapFileTransformer extends BatchTransformer
{
    /**
     * @param MapFile $entity
     *
     * @throws Exception
     *
     * @return \App\Api\Entity\Edit\MapFile
     */
    public function toApi($entity)
    {
        $mapFile = new \App\Api\Entity\Edit\MapFile($entity->getId());
        $mapFile->setCreatedAt($entity->getCreatedAt());
        $mapFile->setFilename($entity->getFilename());
        $mapFile->setMapId($entity->getMap() ? $entity->getMap()->getId() : null);
        $mapFile->setIssueCount($entity->getIssuePositions()->count());

        return $mapFile;
    }
}
