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

use App\DataFixtures\Production\LoadConstructionManagerData;
use App\Entity\ConstructionManager;
use App\Service\Interfaces\UserCreationServiceInterface;
use App\Service\Ldap\LdapLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Ldap;

class UserCreationService implements UserCreationServiceInterface
{
    const AUTHENTICATION_SOURCE_LDAP = 'ldap';
    const AUTHENTICATION_SOURCE_NONE = 'none';

    /**
     * @var string
     */
    private $ldapUrl;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UserCreationService constructor.
     *
     * @param ParameterBagInterface $parameterBag
     * @param LoggerInterface $logger
     */
    public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger)
    {
        $this->ldapUrl = $parameterBag->get('LDAP_URL');
        $this->logger = $logger;
    }

    /**
     * queries LDAP and returns true if connection successful and user exists.
     *
     * @param string $ldapUrl
     * @param string $email
     *
     * @return Entry
     */
    private function getLdapUser(string $ldapUrl, string $email)
    {
        if (!startsWith('ldap://')) {
            $this->logger->log(LogLevel::ERROR, 'invalid connection string: must start with ldap://');

            return null;
        }

        $arguments = explode('/', mb_substr($ldapUrl, 7));
        if (\count($arguments) < 3) {
            $this->logger->log(LogLevel::ERROR, 'invalid connection string: too few arguments');

            return null;
        }

        $argumentIndex = 0;

        // resolve LDAP connection
        $adapter = new Adapter(['connection_string' => 'ldap://' . $arguments[$argumentIndex++]]);
        $ldap = new LdapLogger(new Ldap($adapter), $this->logger);

        // bind to searchdn if necessary
        if (\count($arguments) === 4) {
            [$searchDn, $searchPassword] = explode(':', $arguments[$argumentIndex++]);
            $ldap->bind($searchDn, $searchPassword);
        }

        // create query
        $query = $arguments[$argumentIndex++];
        if (mb_strpos($query, 'username') !== false) {
            $query = str_replace($query, 'username', mb_substr($email, 0, mb_strpos($email, '@')));
        } elseif (mb_strpos($query, 'email')) {
            $query = str_replace($query, 'email', $email);
        }

        // prepare query
        $baseDn = $arguments[$argumentIndex];
        $search = $ldap->query($baseDn, $query);

        // execute & ensure result returned
        /** @var Entry[] $entries */
        $entries = $search->execute();
        $this->logger->log(LogLevel::INFO, 'query has ' . \count($entries) . ' results');

        return \count($entries) > 0 ? $entries[0] : null;
    }

    /**
     * @param Entry $entry
     * @param string $key
     *
     * @return array|mixed|string|null
     */
    private function getAttributeStringValue(Entry $entry, string $key)
    {
        $attributeValue = $entry->getAttribute($key);
        if ($attributeValue !== null) {
            if (\is_array($attributeValue) && \count($attributeValue) > 0) {
                return trim((string)$attributeValue[0]);
            } elseif (\is_string($attributeValue)) {
                return trim($attributeValue);
            }
        }

        return '';
    }

    /**
     * @param Entry $entry
     *
     * @return string|null
     */
    private function parseGivenName(Entry $entry)
    {
        return $this->getAttributeStringValue($entry, 'givenName');
    }

    /**
     * @param Entry $entry
     *
     * @return string|null
     */
    private function parseFamilyName(Entry $entry, string $givenName)
    {
        $attributeValue = $this->getAttributeStringValue($entry, 'familyName');
        if (!empty($attributeValue)) {
            return $attributeValue;
        }

        $attributeValue = $this->getAttributeStringValue($entry, 'name');
        if (!empty($attributeValue)) {
            return trim(mb_substr($attributeValue, mb_strlen($givenName)));
        }

        return '';
    }

    /**
     * @param Entry $entry
     *
     * @return string|null
     */
    private function parsePhone(Entry $entry)
    {
        return $this->getAttributeStringValue($entry, 'phone');
    }

    /**
     * @param ConstructionManager $constructionManager
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function tryAuthenticateConstructionManager(ConstructionManager $constructionManager)
    {
        if ($constructionManager->getAuthenticationSource() === null) {
            $constructionManager->setAuthenticationSource(empty($this->ldapUrl) ? self::AUTHENTICATION_SOURCE_NONE : self::AUTHENTICATION_SOURCE_LDAP);
        }

        if ($constructionManager->getAuthenticationSource() === self::AUTHENTICATION_SOURCE_LDAP) {
            $ldapUser = $this->getLdapUser($this->ldapUrl, $constructionManager->getEmail());
            if ($ldapUser === null) {
                return false;
            }

            $constructionManager->setAuthenticationSource(self::AUTHENTICATION_SOURCE_LDAP);
            $constructionManager->setGivenName($this->parseGivenName($ldapUser));
            $constructionManager->setFamilyName($this->parseFamilyName($ldapUser, $constructionManager->getGivenName()));
            $constructionManager->setPhone($this->parsePhone($ldapUser));

            return true;
        } elseif ($constructionManager->getAuthenticationSource() === self::AUTHENTICATION_SOURCE_NONE || $constructionManager->getAuthenticationSource() === LoadConstructionManagerData::AUTHENTICATION_SOURCE_FIXTURES) {
            return true;
        } else {
            // deny by default
            return false;
        }
    }
}
