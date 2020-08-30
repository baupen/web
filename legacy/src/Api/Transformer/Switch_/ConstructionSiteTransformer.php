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
use App\Security\Model\UserToken;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    public function __construct(\App\Api\Transformer\Base\ConstructionSiteTransformer $constructionSiteTransformer, RouterInterface $router, TokenStorageInterface $tokenStorage, RegistryInterface $registry)
    {
        $this->constructionSiteTransformer = $constructionSiteTransformer;
        $this->router = $router;

        /** @var UserToken $userToken */
        $userToken = $tokenStorage->getToken()->getUser();
        $this->user = $registry->getRepository(ConstructionManager::class)->fromUserToken($userToken);
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

        if (null !== $entity->getImage()) {
            $constructionSite->setImageMedium($this->router->generate('image_construction_site', ['constructionSite' => $entity->getId(), 'image' => $entity->getImage()->getId(), 'size' => ImageServiceInterface::SIZE_MEDIUM]));
        }

        $constructionSite->setCreatedAt($entity->getCreatedAt());
        $constructionSite->setSwitchLink($this->router->generate('switch_switch', ['constructionSite' => $entity->getId()]));
        $constructionSite->setAddress($entity->getAddressLines());
        $constructionSite->setIsConstructionManagerOf($entity->getConstructionManagers()->contains($this->user));

        $managers = [];
        foreach ($entity->getConstructionManagers() as $constructionManager) {
            if ($constructionManager !== $this->user) {
                $managers[] = $constructionManager->getName();
            }
        }
        $constructionSite->setOtherConstructionManagers($managers);

        return $constructionSite;
    }
}
