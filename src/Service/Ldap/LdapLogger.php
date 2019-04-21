<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Ldap;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Ldap\Adapter\EntryManagerInterface;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;

class LdapLogger implements LdapInterface
{
    /**
     * @var LdapInterface
     */
    private $ldap;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LdapLogger constructor.
     *
     * @param LdapInterface   $adapter
     * @param LoggerInterface $logger
     */
    public function __construct(LdapInterface $adapter, LoggerInterface $logger)
    {
        $this->ldap = $adapter;
        $this->logger = $logger;
    }

    /**
     * Fetches the entry manager instance.
     *
     * @return EntryManagerInterface
     */
    public function getEntryManager()
    {
        return $this->ldap->getEntryManager();
    }

    /**
     * Escape a string for use in an LDAP filter or DN.
     *
     * @param string $subject
     * @param string $ignore
     * @param int    $flags
     *
     * @return string
     */
    public function escape($subject, $ignore = '', $flags = 0)
    {
        return $this->ldap->escape($subject, $ignore, $flags);
    }

    /**
     * Return a connection bound to the ldap.
     *
     * @param string $dn       A LDAP dn
     * @param string $password A password
     *
     * @throws ConnectionException if dn / password could not be bound
     */
    public function bind($dn = null, $password = null)
    {
        $this->logger->log(LogLevel::INFO, 'bind', ['dn' => $dn, 'password' => $password]);

        return $this->ldap->bind($dn, $password);
    }

    /**
     * Queries a ldap server for entries matching the given criteria.
     *
     * @param string $dn
     * @param string $query
     * @param array  $options
     *
     * @return QueryInterface
     */
    public function query($dn, $query, array $options = [])
    {
        $this->logger->log(LogLevel::INFO, 'create query ', ['dn' => $dn, 'query' => $query, 'options' => $options]);

        return $this->ldap->query($dn, $query, $options);
    }
}
