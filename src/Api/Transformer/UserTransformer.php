<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:20 PM
 */

namespace App\Api\Transformer;


use App\Api\Entity\ObjectMeta;
use App\Api\Entity\User;
use App\Entity\ConstructionManager;

class UserTransformer
{
    /**
     * @param ConstructionManager $constructionManager
     * @param string $authenticationToken
     * @return User
     */
    public function transform(ConstructionManager $constructionManager, string $authenticationToken)
    {
        $user = new User();
        $user->setAuthenticationToken($authenticationToken);
        $user->setGivenName($constructionManager->getGivenName());
        $user->setFamilyName($constructionManager->getFamilyName());
        return $user;
    }
}