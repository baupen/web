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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
     * @var \stdClass[]|null
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
     *
     * @param PathServiceInterface  $pathService
     * @param LoggerInterface       $logger
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(PathServiceInterface $pathService, LoggerInterface $logger, ParameterBagInterface $parameterBag)
    {
        $this->pathService = $pathService;
        $this->logger = $logger;

        $this->authorizationMethod = $parameterBag->get('AUTHORIZATION_METHOD');
    }

    /**
     * @param string $email
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function checkIfAuthorized(string $email)
    {
        if ($this->authorizationMethod === self::AUTHORIZATION_METHOD_NONE) {
            return true;
        }

        if ($this->authorizationMethod === self::AUTHORIZATION_METHOD_WHITELIST) {
            $emailLookup = $this->getAllWhitelistedEmailLookup();

            return array_key_exists($email, $emailLookup);
        }

        throw new \Exception('invalid authorization method configured: ' . $this->authorizationMethod);
    }

    /**
     * @param ConstructionManager $constructionManager
     */
    public function tryFillDefaultValues(ConstructionManager $constructionManager)
    {
        $userData = $this->getAllUserData();

        if (!array_key_exists($constructionManager->getEmail(), $userData)) {
            return;
        }

        $defaultValues = $userData[$constructionManager->getEmail()];

        if (property_exists($defaultValues, 'givenName')) {
            $constructionManager->setGivenName($defaultValues->givenName);
        }

        if (property_exists($defaultValues, 'familyName')) {
            $constructionManager->setFamilyName($defaultValues->familyName);
        }

        if (property_exists($defaultValues, 'phone')) {
            $constructionManager->setPhone($defaultValues->phone);
        }
    }

    /**
     * @return string
     */
    private function getAuthorizationRoot()
    {
        return $this->pathService->getTransientFolderRoot() . \DIRECTORY_SEPARATOR . 'authorization';
    }

    /**
     * @return \stdClass[]
     */
    private function getAllUserData()
    {
        if ($this->userDataCache !== null) {
            return $this->userDataCache;
        }

        $this->userDataCache = [];

        $userDataRoot = $this->getAuthorizationRoot() . \DIRECTORY_SEPARATOR . 'user_data';
        foreach (glob($userDataRoot . '/*.json') as $userDataFile) {
            $json = file_get_contents($userDataFile);

            $entries = json_decode($json);
            foreach ($entries as $entry) {
                if (property_exists($entry, 'email')) {
                    $this->userDataCache[$entry->email] = $entry;
                }
            }
        }

        return $this->userDataCache;
    }

    /**
     * @return string[]
     */
    private function getAllWhitelistedEmailLookup()
    {
        if ($this->emailLookupCache !== null) {
            return $this->emailLookupCache;
        }

        $this->emailLookupCache = [];

        $whitelistRoot = $this->getAuthorizationRoot() . \DIRECTORY_SEPARATOR . 'whitelists';
        foreach (glob($whitelistRoot . '/*.txt') as $whitelistFile) {
            $whitelist = file_get_contents($whitelistFile);
            $lines = explode("\n", $whitelist);
            foreach ($lines as $line) {
                $cleanedLine = trim($line);
                if ($cleanedLine !== '') {
                    $this->emailLookupCache[$cleanedLine] = true;
                }
            }
        }

        return $this->emailLookupCache;
    }
}
