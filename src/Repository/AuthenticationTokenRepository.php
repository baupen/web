<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:08 PM
 */

namespace App\Repository;


use App\Entity\AuthenticationToken;
use App\Helper\HashHelper;
use Doctrine\ORM\EntityRepository;

class AuthenticationTokenRepository extends EntityRepository
{
    /**
     * get the construction manager assigned to that authentication token
     * returns null if the token is invalid or not found
     *
     * @param string|null $authenticationToken
     * @return \App\Entity\ConstructionManager|null
     * @throws \Doctrine\ORM\ORMException
     */
    public function getConstructionManager($authenticationToken)
    {
        if (mb_strlen($authenticationToken) != HashHelper::HASH_LENGTH) {
            return null;
        }

        /** @var AuthenticationToken $token */
        $token = $this->findOneBy(["token" => $authenticationToken]);
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