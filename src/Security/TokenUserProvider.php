<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Filter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private ManagerRegistry $manager;

    /**
     * TokenUserProvider constructor.
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    public function supportsClass(string $class): bool
    {
        return AuthenticationToken::class === $class || is_subclass_of($class, AuthenticationToken::class);
    }

    /**
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername(string $username): AuthenticationToken
    {
        $token = new AuthenticationToken();
        $token->setToken($username);

        $constructionManager = $this->manager->getRepository(ConstructionManager::class)->findOneBy(['authenticationToken' => $username, 'isEnabled' => true]);
        if (null !== $constructionManager) {
            $token->setConstructionManager($constructionManager);

            return $token;
        }

        $craftsman = $this->manager->getRepository(Craftsman::class)->findOneBy(['deletedAt' => null, 'authenticationToken' => $username]);
        if (null !== $craftsman) {
            $token->setCraftsman($craftsman);

            return $token;
        }

        $filter = $this->manager->getRepository(Filter::class)->findOneBy(['authenticationToken' => $username]);
        if (null !== $filter) {
            if (null === $filter->getAccessAllowedBefore() || $filter->getAccessAllowedBefore() > new \DateTime()) {
                $token->setFilter($filter);

                return $token;
            }
            throw new UserNotFoundException();
        }

        throw new UserNotFoundException();
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     */
    public function refreshUser(UserInterface $user): AuthenticationToken
    {
        if (!$user instanceof AuthenticationToken) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $this->loadUserByUsername($user->getToken());
    }
}
