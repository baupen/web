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

use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\LdapInterface;

class UserCreationService
{
    /**
     * @var Ldap
     */
    private $ldap;

    public function __construct()
    {
        $ldap = new Ldap(new Adapter());

        /*
         *
  Symfony\Component\Ldap\Adapter\ExtLdap\Adapter:
    arguments:
      - host: ldap.forumsys.com
        port: 389
        options:
          protocol_version: 3
          referrals: false



    ldap_provider:
      ldap:
        service: Symfony\Component\Ldap\Ldap
        base_dn: dc=example,dc=com
        search_dn: "cn=read-only-admin,dc=example,dc=com"
        search_password: password
        default_roles: ROLE_USER
        uid_key: uid

        ldapsearch -w password -h ldap.forumsys.com -D "uid=tesla,dc=example,dc=com" -b "dc=example,dc=com"
                     */
    }

    public function tryCreateUser(string $email)
    {
        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $username = $this->ldap->escape($email, '', LdapInterface::ESCAPE_FILTER);
            $query = str_replace('{username}', $username, $this->defaultSearch);
            $search = $this->ldap->query($this->baseDn, $query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
        }

        $entries = $search->execute();
        $count = \count($entries);

        if (!$count) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        if ($count > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }

        $entry = $entries[0];

        try {
            if (null !== $this->uidKey) {
                $username = $this->getAttributeValue($entry, $this->uidKey);
            }
        } catch (InvalidArgumentException $e) {
        }

        return false;
    }
}
