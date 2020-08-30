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

use App\Api\External\Entity\File;
use App\Entity\Traits\FileTrait;
use App\Entity\Traits\IdTrait;

class FileTransformer
{
    /**
     * @param IdTrait|FileTrait $entity
     *
     * @return File
     */
    public function toApi($entity)
    {
        if (null === $entity) {
            return null;
        }

        $file = new File();
        $file->setId($entity->getId());
        $file->setFilename($entity->getFilename());

        return $file;
    }
}
