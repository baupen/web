<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Dashboard;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\ConstructionSite;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\Routing\RouterInterface;

class ConstructionSiteTransformer extends BatchTransformer
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
     *
     * @param \App\Api\Transformer\Base\ConstructionSiteTransformer $constructionSiteTransformer
     * @param RouterInterface $router
     */
    public function __construct(\App\Api\Transformer\Base\ConstructionSiteTransformer $constructionSiteTransformer, RouterInterface $router)
    {
        $this->constructionSiteTransformer = $constructionSiteTransformer;
        $this->router = $router;
    }

    /**
     * @param ConstructionSite $entity
     * @param \App\Api\Entity\Dashboard\ConstructionSite $constructionSite
     */
    public function writeApiProperties(ConstructionSite $entity, \App\Api\Entity\Dashboard\ConstructionSite $constructionSite)
    {
        $this->constructionSiteTransformer->writeApiProperties($entity, $constructionSite);

        if ($entity->getImage() !== null) {
            $constructionSite->setImageMedium($this->router->generate('image_construction_site', ['constructionSite' => $entity->getId(), 'imageId' => $entity->getImage()->getId(), 'size' => ImageServiceInterface::SIZE_MEDIUM]));
        }
    }

    /**
     * @param ConstructionSite $entity
     *
     * @return \App\Api\Entity\Dashboard\ConstructionSite
     */
    public function toApi($entity)
    {
        $constructionSite = new \App\Api\Entity\Dashboard\ConstructionSite($entity->getId());
        $this->writeApiProperties($entity, $constructionSite);

        return $constructionSite;
    }
}
