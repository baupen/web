<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Api\External\Request\Base\AuthenticatedRequest;
use App\Entity\AuthenticationToken;
use App\Helper\HashHelper;
use Doctrine\ORM\EntityRepository;

class AuthenticationTokenRepository extends EntityRepository
{
    /**
     * get the construction manager assigned to that authentication token
     * returns null if the token is invalid or not found.
     *
     * @param AuthenticatedRequest $authenticatedRequest
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return \App\Entity\ConstructionManager|null
     */
    public function getConstructionManager(AuthenticatedRequest $authenticatedRequest)
    {
        if ($authenticatedRequest === null || mb_strlen($authenticatedRequest->getAuthenticationToken()) !== HashHelper::HASH_LENGTH) {
            return null;
        }

        /** @var AuthenticationToken $token */
        $token = $this->findOneBy(['token' => $authenticatedRequest->getAuthenticationToken()]);
        if ($token !== null) {
            // remember last used date
            $token->setLastUsed();
            $this->getEntityManager()->flush();

            // return user
            return $token->getConstructionManager();
        }

        return null;
    }
}
