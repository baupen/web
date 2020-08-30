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
use App\Entity\Craftsman;

class CraftsmanTransformer extends BatchTransformer
{
    /**
     * @var ObjectMetaTransformer
     */
    private $objectMetaTransformer;

    public function __construct(ObjectMetaTransformer $objectMetaTransformer)
    {
        $this->objectMetaTransformer = $objectMetaTransformer;
    }

    /**
     * @param Craftsman $entity
     *
     * @return \App\Api\External\Entity\Craftsman
     */
    public function toApi($entity)
    {
        $craftsman = new \App\Api\External\Entity\Craftsman();
        $craftsman->setName($entity->getName());
        $craftsman->setTrade($entity->getTrade());
        $craftsman->setConstructionSiteID($entity->getConstructionSite()->getId());

        $craftsman->setMeta($this->objectMetaTransformer->toApi($entity));

        return $craftsman;
    }
}
