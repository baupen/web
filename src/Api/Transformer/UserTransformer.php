<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @param string              $authenticationToken
     *
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
