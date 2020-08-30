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

use App\Entity\ConstructionSite;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\Routing\RouterInterface;

class ConstructionSiteTransformer
{
    /**
     * @var \App\Api\Transformer\Base\ConstructionSiteTransformer
     */
    private $constructionSiteTransformer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * CraftsmanTransformer constructor.
     */
    public function __construct(\App\Api\Transformer\Base\ConstructionSiteTransformer $constructionSiteTransformer, RouterInterface $router)
    {
        $this->constructionSiteTransformer = $constructionSiteTransformer;
        $this->router = $router;
    }

    /**
     * @param ConstructionSite $entity
     *
     * @return \App\Api\Entity\Edit\ConstructionSite
     */
    public function toApi($entity)
    {
        $constructionSite = new \App\Api\Entity\Edit\ConstructionSite($entity->getId());
        $this->constructionSiteTransformer->writeApiProperties($entity, $constructionSite);

        $constructionSite->setStreetAddress($entity->getStreetAddress());
        $constructionSite->setPostalCode($entity->getPostalCode());
        $constructionSite->setLocality($entity->getLocality());
        $constructionSite->setCountry($entity->getCountry());

        if (null !== $entity->getImage()) {
            $constructionSite->setImageMedium($this->router->generate('image_construction_site', ['constructionSite' => $entity->getId(), 'image' => $entity->getImage()->getId(), 'size' => ImageServiceInterface::SIZE_MEDIUM]));
        }

        return $constructionSite;
    }
}
