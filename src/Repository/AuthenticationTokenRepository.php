<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:08 PM
 */

namespace App\Repository;


use App\Api\Request\Base\AuthenticatedRequest;
use App\Entity\AuthenticationToken;
use App\Helper\HashHelper;
use Doctrine\ORM\EntityRepository;

class AuthenticationTokenRepository extends EntityRepository
{
    /**
     * get the construction manager assigned to that authentication token
     * returns null if the token is invalid or not found
     *
     * @param AuthenticatedRequest $authenticatedRequest
     * @return \App\Entity\ConstructionManager|null
     * @throws \Doctrine\ORM\ORMException
     */
    public function getConstructionManager(AuthenticatedRequest $authenticatedRequest)
    {
        if ($authenticatedRequest == null || mb_strlen($authenticatedRequest->getAuthenticationToken()) != HashHelper::HASH_LENGTH) {
            return null;
        }

        /** @var AuthenticationToken $token */
        $token = $this->findOneBy(["token" => $authenticatedRequest->getAuthenticationToken()]);
        if ($token != null) {
            // remember last used date
            $token->setLastUsed();
            $this->getEntityManager()->flush();

            // return user
            return $token->getConstructionManager();
        }
        return null;
    }
}