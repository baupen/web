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

use App\Entity\IssuePosition;

class IssuePositionTransformer
{
    /**
     * @param IssuePosition $entity
     *
     * @return \App\Api\External\Entity\IssuePosition
     */
    public function toApi($entity)
    {
        $position = new \App\Api\External\Entity\IssuePosition();
        $position->setX($entity->getPositionX());
        $position->setY($entity->getPositionY());
        $position->setZoomScale($entity->getPositionZoomScale());
        $position->setMapFileId($entity->getMapFile()->getId());

        return $position;
    }
}
