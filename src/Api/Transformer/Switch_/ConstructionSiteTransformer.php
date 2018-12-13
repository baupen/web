<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Switch_;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

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
     * @var ConstructionManager
     */
    private $user;

    public function __construct(self $constructionSiteTransformer, RouterInterface $router, TokenStorage $tokenStorage)
    {
        $this->constructionSiteTransformer = $constructionSiteTransformer;
        $this->router = $router;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param ConstructionSite $entity
     *
     * @return \App\Api\Entity\Switch_\ConstructionSite
     */
    public function toApi($entity)
    {
        $constructionSite = new \App\Api\Entity\Switch_\ConstructionSite($entity->getId());
        $this->constructionSiteTransformer->writeApiProperties($entity, $constructionSite);

        if ($entity->getImage() !== null) {
            $constructionSite->setImageMedium($this->router->generate('image_construction_site', ['constructionSite' => $entity->getId(), 'image' => $entity->getImage()->getId(), 'size' => ImageServiceInterface::SIZE_MEDIUM]));
        }

        $constructionSite->setIsConstructionManagerOf($entity->getConstructionManagers()->contains($this->user));

        return $constructionSite;
    }
}
