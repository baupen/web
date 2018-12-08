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

use App\Entity\MapFile;

class MapFileTransformer
{
    /**
     * @param MapFile $entity
     *
     * @throws \Exception
     *
     * @return \App\Api\Entity\Edit\MapFile
     */
    public function toApi($entity)
    {
        $craftsman = new \App\Api\Entity\Edit\MapFile($entity->getId());
        $craftsman->setCreatedAt($entity->getCreatedAt());
        $craftsman->setFilename($entity->getFilename());

        return $craftsman;
    }
}
