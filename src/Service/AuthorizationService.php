<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionManager;
use App\Service\Interfaces\AuthorizationServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Psr\Log\LoggerInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    const AUTHORIZATION_METHOD_NONE = 'none';
    const AUTHORIZATION_METHOD_WHITELIST = 'whitelist';

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[][]|null
     */
    private $userDataCache;

    /**
     * @var string[]|null
     */
    private $emailLookupCache;

    /**
     * @var string
     */
    private $authorizationMethod;

    /**
     * AuthorizationService constructor.
     */
    public function __construct(PathServiceInterface $pathService, LoggerInterface $logger, string $authorizationMethod)
    {
        $this->pathService = $pathService;
        $this->logger = $logger;
        $this->authorizationMethod = $authorizationMethod;
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function checkIfAuthorized(ConstructionManager $constructionManager)
    {
        if (self::AUTHORIZATION_METHOD_NONE === $this->authorizationMethod) {
            return true;
        }

        if ($constructionManager->getIsExternalAccount() || $constructionManager->getIsTrialAccount()) {
            return true;
        }

        if (self::AUTHORIZATION_METHOD_WHITELIST === $this->authorizationMethod) {
            return $this->isEMailOnWhitelist($constructionManager->getEmail());
        }

        throw new \Exception('invalid authorization method configured: '.$this->authorizationMethod);
    }

    public function tryFillDefaultValues(ConstructionManager $constructionManager)
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

    /**
     * @return string
     */
    private function getAuthorizationRoot()
    {
        return $this->pathService->getTransientFolderRoot().\DIRECTORY_SEPARATOR.'authorization';
    }

    /**
     * @return string[]
     */
    private function getDefaultUserData(string $email): array
    {
        if (null == $this->userDataCache) {
            $this->userDataCache = [];

            $userDataRoot = $this->getAuthorizationRoot().\DIRECTORY_SEPARATOR.'user_data';
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

    private function isEMailOnWhitelist(string $email)
    {
        if (null == $this->emailLookupCache) {
            $this->emailLookupCache = [];

            $whitelistRoot = $this->getAuthorizationRoot().\DIRECTORY_SEPARATOR.'whitelists';
            foreach (glob($whitelistRoot.\DIRECTORY_SEPARATOR.'*.txt') as $whitelistFile) {
                $whitelist = file_get_contents($whitelistFile);
                $lines = explode("\n", $whitelist);
                foreach ($lines as $line) {
                    $cleanedLine = trim($line);
                    if ('' !== $cleanedLine) {
                        $this->emailLookupCache[$cleanedLine] = true;
                    }
                }
            }
        }

        return \array_key_exists($email, $this->emailLookupCache);
    }
}
