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
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * This "UserProvider" mocks a real user provider.
 * It hands out an authentication token if the
 * Class AuthenticationTokenUserProvider.
 */
class AuthenticationTokenUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private ManagerRegistry $manager;

    /**
     * AuthenticationTokenUserProvider constructor.
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername(string $username)
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
            } else {
                throw new UsernameNotFoundException();
            }
        }

        throw new UsernameNotFoundException();
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof AuthenticationToken) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $this->loadUserByUsername($user->getToken());
    }

    public function supportsClass(string $class)
    {
        return AuthenticationToken::class === $class || is_subclass_of($class, AuthenticationToken::class);
    }

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        // intentionally skipped, as no passwords are stored

        // when encoded passwords are in use, this method should:
        // 1. persist the new password in the user storage
        // 2. update the $user object with $user->setPassword($newEncodedPassword);
    }
}
