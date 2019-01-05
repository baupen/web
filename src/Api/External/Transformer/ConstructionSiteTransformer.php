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

use App\Api\External\Entity\Address;
use App\Api\External\Entity\ConstructionSite;
use App\Api\External\Transformer\Base\BatchTransformer;

class ConstructionSiteTransformer extends BatchTransformer
{
    /**
     * @var ObjectMetaTransformer
     */
    private $objectMetaTransformer;

    /**
     * @var FileTransformer
     */
    private $fileTransformer;

    public function __construct(ObjectMetaTransformer $objectMetaTransformer, FileTransformer $fileTransformer)
    {
        $this->objectMetaTransformer = $objectMetaTransformer;
        $this->fileTransformer = $fileTransformer;
    }

    /**
     * @param \App\Entity\ConstructionSite $entity
     *
     * @return ConstructionSite
     */
    public function toApi($entity)
    {
        $building = new ConstructionSite();
        $building->setName($entity->getName());
        $building->setImage($this->fileTransformer->toApi($entity->getImage()));

        $childrenIds = [];
        foreach ($entity->getMaps() as $child) {
            if ($child->getParent() === null) {
                $childrenIds[] = $child->getId();
            }
        }
        $building->setMaps($childrenIds);

        $childrenIds = [];
        foreach ($entity->getCraftsmen() as $child) {
            $childrenIds[] = $child->getId();
        }
        $building->setCraftsmen($childrenIds);

        $address = new Address();
        $address->setStreetAddress($entity->getStreetAddress());
        $address->setLocality($entity->getLocality());
        $address->setPostalCode($entity->getPostalCode());
        $address->setCountry($entity->getCountry());
        $building->setAddress($address);

        $building->setMeta($this->objectMetaTransformer->toApi($entity));

        return $building;
    }
}
