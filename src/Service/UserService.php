<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionManager;
use App\Helper\DoctrineHelper;
use App\Service\Interfaces\EmailServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\UserServiceInterface;
use Doctrine\Persistence\ManagerRegistry;

class UserService implements UserServiceInterface
{
    public const AUTHORIZATION_METHOD_DEFAULT_ALLOW_SELF_ASSOCIATION = 'default_allow_self_association';
    public const AUTHORIZATION_METHOD_DEFAULT_DISALLOW_SELF_ASSOCIATION = 'default_disallow_self_association';
    public const AUTHORIZATION_METHOD_WHITELIST = 'whitelist';

    private PathServiceInterface $pathService;

    private EmailServiceInterface $emailService;

    private ManagerRegistry $registry;

    /**
     * @var string[][]|null
     */
    private ?array $userDataCache = null;

    /**
     * @var string[]|null
     */
    private ?array $emailWhitelistCache = null;

    /**
     * @var string[]|null
     */
    private ?array $domainWhitelistCache = null;

    private string $authorizationMethod;

    /**
     * AuthorizationService constructor.
     */
    public function __construct(PathServiceInterface $pathService, ManagerRegistry $manager, EmailServiceInterface $emailService, string $authorizationMethod)
    {
        $this->pathService = $pathService;
        $this->registry = $manager;
        $this->emailService = $emailService;
        $this->authorizationMethod = $authorizationMethod;
    }

    public function authorize(ConstructionManager $constructionManager): void
    {
        switch ($this->authorizationMethod) {
            case self::AUTHORIZATION_METHOD_DEFAULT_ALLOW_SELF_ASSOCIATION:
                $constructionManager->setCanAssociateSelf(true);
                break;
            case self::AUTHORIZATION_METHOD_DEFAULT_DISALLOW_SELF_ASSOCIATION:
                $constructionManager->setCanAssociateSelf(false);
                break;
            case self::AUTHORIZATION_METHOD_WHITELIST:
                $this->doWhitelistAuthorization($constructionManager);
                break;
            default:
                throw new \Exception('invalid authorization method configured: '.$this->authorizationMethod);
        }
    }

    public function refreshAuthorization(ConstructionManager $constructionManager): void
    {
        switch ($this->authorizationMethod) {
            case self::AUTHORIZATION_METHOD_DEFAULT_ALLOW_SELF_ASSOCIATION:
            case self::AUTHORIZATION_METHOD_DEFAULT_DISALLOW_SELF_ASSOCIATION:
                break;
            case self::AUTHORIZATION_METHOD_WHITELIST:
                $this->doWhitelistAuthorization($constructionManager);
                break;
            default:
                throw new \Exception('invalid authorization method configured: '.$this->authorizationMethod);
        }
    }

    public function tryRegister(ConstructionManager $template, ?string &$error = null): bool
    {
        /** @var ConstructionManager $existing */
        $existing = $this->registry->getRepository(ConstructionManager::class)->findOneBy(['email' => $template->getEmail()]);

        if (null !== $existing && $existing->getRegistrationCompleted()) {
            $error = UserServiceInterface::REGISTRATION_FAIL_ALREADY_REGISTERED;

            return false;
        }

        if (null !== $existing) {
            $template = $existing;
        }

        if (!$template->getIsEnabled()) {
            $error = UserServiceInterface::REGISTRATION_FAIL_ACCOUNT_DISABLED;

            return false;
        }

        $template->setAuthenticationHash();
        DoctrineHelper::persistAndFlush($this->registry, $template);

        if (!$this->emailService->sendRegisterConfirmLink($template)) {
            $error = UserServiceInterface::REGISTRATION_FAIL_EMAIL_NOT_SENT;

            return false;
        }

        return true;
    }

    public function setDefaultValues(ConstructionManager $constructionManager): void
    {
        $defaultValues = $this->getDefaultUserData($constructionManager->getEmail());

        if (\array_key_exists('givenName', $defaultValues)) {
            $constructionManager->setGivenName($defaultValues['givenName']);
        }

        if (\array_key_exists('familyName', $defaultValues)) {
            $constructionManager->setFamilyName($defaultValues['familyName']);
        }

        if (\array_key_exists('phone', $defaultValues)) {
            $constructionManager->setPhone($defaultValues['phone']);
        }
    }

    private function doWhitelistAuthorization(ConstructionManager $constructionManager): void
    {
        if ($this->isEmailOnWhitelist($constructionManager->getEmail())) {
            // is on whitelist
            $constructionManager->setAuthorizationAuthority(ConstructionManager::AUTHORIZATION_AUTHORITY_WHITELIST);
            $constructionManager->setCanAssociateSelf(true);
            $constructionManager->setIsEnabled(true);
        } elseif (ConstructionManager::AUTHORIZATION_AUTHORITY_WHITELIST === $constructionManager->getAuthorizationAuthority()) {
            // was on whitelist, but not anymore
            $constructionManager->setIsEnabled(false);
        } else {
            $constructionManager->setCanAssociateSelf(false);
        }
    }

    private function isEmailOnWhitelist(string $email)
    {
        if (null == $this->emailWhitelistCache) {
            $this->emailWhitelistCache = [];
            $this->domainWhitelistCache = [];

            $whitelistRoot = $this->pathService->getTransientFolderForAuthorization().\DIRECTORY_SEPARATOR.'whitelists';
            $domainWhitelistPath = $whitelistRoot.\DIRECTORY_SEPARATOR.'domains.txt';
            foreach (glob($whitelistRoot.\DIRECTORY_SEPARATOR.'*.txt') as $whitelistFile) {
                $whitelist = file_get_contents($whitelistFile);
                $lines = explode("\n", $whitelist);
                foreach ($lines as $line) {
                    $cleanedLine = trim($line);
                    if ('' === $cleanedLine) {
                        continue;
                    }

                    if ($whitelistFile === $domainWhitelistPath) {
                        $this->domainWhitelistCache[] = $cleanedLine;
                    } else {
                        $this->emailWhitelistCache[] = $cleanedLine;
                    }
                }
            }
        }

        if (in_array($email, $this->emailWhitelistCache)) {
            return true;
        }

        if (0 === count($this->domainWhitelistCache)) {
            return false;
        }

        $domainPart = substr($email, strrpos($email, '@') + 1);

        return in_array($domainPart, $this->domainWhitelistCache, true);
    }

    /**
     * @return string[]
     */
    private function getDefaultUserData(string $email): array
    {
        if (null == $this->userDataCache) {
            $this->userDataCache = [];

            $userDataRoot = $this->pathService->getTransientFolderForAuthorization().\DIRECTORY_SEPARATOR.'user_data';
            foreach (glob($userDataRoot.\DIRECTORY_SEPARATOR.'*.json') as $userDataFile) {
                $json = file_get_contents($userDataFile);

                $entries = json_decode($json, true);
                foreach ($entries as $entry) {
                    if (\array_key_exists('email', $entry)) {
                        $this->userDataCache[$entry['email']] = $entry;
                    }
                }
            }
        }

        if (!\array_key_exists($email, $this->userDataCache)) {
            return [];
        }

        return $this->userDataCache[$email];
    }
}
