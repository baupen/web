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
use Symfony\Component\Ldap\Exception\ConnectionException;
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
        if (mb_strpos($ldapUrl, 'ldap://') !== 0) {
            $this->logger->log(LogLevel::ERROR, 'invalid connection string: must start with ldap://');

            return null;
        }

        $arguments = explode('/', mb_substr($ldapUrl, 7));
        if (\count($arguments) !== 4) {
            $this->logger->log(LogLevel::ERROR, 'invalid connection string: too few arguments');

            return null;
        }

        $argumentIndex = 0;

        // resolve LDAP connection
        [$host, $port] = explode(':', $arguments[$argumentIndex++]);
        $adapter = new Adapter(['host' => $host, 'port' => $port, 'debug' => true]);
        $ldap = new LdapLogger(new Ldap($adapter), $this->logger);

        // bind to searchdn
        [$searchDn, $searchPassword] = explode(':', $arguments[$argumentIndex++]);
        $ldap->bind($searchDn, $searchPassword);

        // get base dn
        $baseDn = $arguments[$argumentIndex++];

        // create query
        $query = $arguments[$argumentIndex];
        if (mb_strpos($query, 'username') !== false) {
            $username = mb_substr($email, 0, mb_strpos($email, '@'));
            $query = str_replace('username', $username, $query);
        } elseif (mb_strpos($query, 'email')) {
            $query = str_replace('email', $email, $query);
        }

        // prepare query
        try {
            $search = $ldap->query($baseDn, $query, ['timeout' => 2]);
        } catch (ConnectionException $exception) {
            $this->logger->log(LogLevel::ERROR, 'can\'t connection to LDAP server', ['exception' => $exception]);

            return null;
        }
        var_dump(2);

        // execute & ensure result returned
        /** @var Entry[] $entries */
        $entries = $search->execute();
        $this->logger->log(LogLevel::INFO, 'query has ' . \count($entries) . ' results');

        return \count($entries) > 0 ? $entries[0] : null;
    }

    private function alterative(string $email)
    {
        $ldap = ldap_connect('ldap://192.168.16.33:389');

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldap, LDAP_OPT_TIMEOUT, 2);
        ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 2);

        $bind = @ldap_bind($ldap, 'uid=tesla,dc=example,dc=com', 'password');

        if ($bind) {
            $result = ldap_search($ldap, 'dc=example,dc=com', '(uid=training)');
            $info = ldap_get_entries($ldap, $result);
            @ldap_close($ldap);

            return \count($info) > 0;
        }

        return false;
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
            return $this->alterative($constructionManager->getEmail());
            $ldapUser = $this->getLdapUser($this->ldapUrl, $constructionManager->getEmail());
            var_dump($ldapUser);
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
        }
        // deny by default
        return false;
    }
}
