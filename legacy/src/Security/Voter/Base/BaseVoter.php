<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter\Base;

use App\Entity\ConstructionManager;
use App\Security\Model\UserToken;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class BaseVoter extends Voter
{
    const ANY_ATTRIBUTE = 'any';

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * BaseVoter constructor.
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return mixed
     */
    protected function getUser(TokenInterface $token)
    {
        /** @var UserToken $userToken */
        $userToken = $token->getUser();

        return $this->registry->getRepository(ConstructionManager::class)->fromUserToken($userToken);
    }
}
