<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:26 PM
 */

namespace App\Service;


use App\Api\Entity\ObjectMeta;
use App\Api\Entity\User;
use App\Api\Transformer\UserTransformer;
use App\Entity\ConstructionManager;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Service\Interfaces\ApiEntityConversionServiceInterface;

class ApiEntityConversionService implements ApiEntityConversionServiceInterface
{
    private $userTransformer;

    /**
     * ApiEntityConversionService constructor.
     */
    public function __construct()
    {
        $this->userTransformer = new UserTransformer();
    }

    /**
     * @param ConstructionManager $constructionManager
     * @param string|null $authenticationToken
     *
     * @return User
     */
    public function convertToUser(ConstructionManager $constructionManager, $authenticationToken)
    {
        $user = $this->userTransformer->transform($constructionManager, $authenticationToken);
        $user->setMeta($this->getObjectMeta($constructionManager));
        return $user;
    }

    /**
     * @param IdTrait|TimeTrait $entity
     * @return ObjectMeta
     */
    private function getObjectMeta($entity)
    {
        $meta = new ObjectMeta();
        $meta->setId($entity->getId());
        $meta->setLastChangeTime($entity->getLastChangedAt()->format("c"));
        return $meta;

    }
}