<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:20 PM
 */

namespace App\Api\Transformer;


use App\Api\Entity\User;
use App\Entity\ConstructionManager;

class UserTransformer
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
     * @param ConstructionManager $constructionManager
     * @param string $authenticationToken
     * @return User
     */
    public function toApi(ConstructionManager $constructionManager, string $authenticationToken)
    {
        $user = new User();
        $user->setAuthenticationToken($authenticationToken);
        $user->setGivenName($constructionManager->getGivenName());
        $user->setFamilyName($constructionManager->getFamilyName());
        $user->setMeta($this->objectMetaTransformer->toApi($constructionManager));
        return $user;
    }
}