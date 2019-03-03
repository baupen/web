<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Service;

use App\Entity\ConstructionManager;
use App\Service\UserAuthenticationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class UserAuthenticationServiceTest extends WebTestCase
{
    /**
     * TrialServiceTest constructor.
     *
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        self::bootKernel();
    }

    /**
     * check whether the new user has the trial account boolean set.
     *
     * @throws \Exception
     */
    public function ignored_testCreateTrialAccount_ldapEnabled_authenticatesValidEmail()
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail('training@example.com');
        $service = $this->getServiceWithLdap();

        $result = $service->tryAuthenticateConstructionManager($constructionManager);

        $this->assertTrue($result);
        $this->assertSame(UserAuthenticationService::AUTHENTICATION_SOURCE_LDAP, $constructionManager->getAuthenticationSource());
    }

    /**
     * check whether the new user has the trial account boolean set.
     *
     * @throws \Exception
     */
    public function ignored_testCreateTrialAccount_ldapEnabled_deniesInvalidEmail()
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail('invalid@example.com');
        $service = $this->getServiceWithLdap();

        $result = $service->tryAuthenticateConstructionManager($constructionManager);

        $this->assertFalse($result);
        $this->assertNull($constructionManager->getAuthenticationSource());
    }

    /**
     * check whether the new user has the trial account boolean set.
     *
     * @throws \Exception
     */
    public function testCreateTrialAccount_noRestrictions_acceptsAll()
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail('something@example.com');
        $service = $this->getServiceWithoutLdap();

        $result = $service->tryAuthenticateConstructionManager($constructionManager);

        $this->assertTrue($result);
        $this->assertSame(UserAuthenticationService::AUTHENTICATION_SOURCE_NONE, $constructionManager->getAuthenticationSource());
    }

    /**
     * check whether the new user has the trial account boolean set.
     *
     * @throws \Exception
     */
    public function testCreateTrialAccount_specificValidRegistrationEmails_acceptsValid()
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail('one@example.com');
        $service = $this->getServiceWithRegistrationEmail();

        $result = $service->tryAuthenticateConstructionManager($constructionManager);

        $this->assertTrue($result);
        $this->assertSame(UserAuthenticationService::AUTHENTICATION_SOURCE_VALID_REGISTRATION_EMAILS, $constructionManager->getAuthenticationSource());
    }

    /**
     * check whether the new user has the trial account boolean set.
     *
     * @throws \Exception
     */
    public function testCreateTrialAccount_specificValidRegistrationEmails_deniesInvalid()
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail('invalid@example.com');
        $service = $this->getServiceWithRegistrationEmail();

        $result = $service->tryAuthenticateConstructionManager($constructionManager);

        $this->assertFalse($result);
        $this->assertSame(UserAuthenticationService::AUTHENTICATION_SOURCE_VALID_REGISTRATION_EMAILS, $constructionManager->getAuthenticationSource());
    }

    /**
     * @return UserAuthenticationService
     */
    private function getServiceWithLdap()
    {
        //ldapsearch -h ldap.forumsys.com -D "uid=tesla,dc=example,dc=com" -b "dc=example,dc=com" -w password "(uid=training)"
        $parameterBag = new ParameterBag(['LDAP_URL' => 'ldap://192.168.16.33:389/uid=tesla,dc=example,dc=com:password/dc=example,dc=com/(uid={username})', 'VALID_REGISTRATION_EMAILS' => 'all']);
        $logger = self::$container->get(LoggerInterface::class);

        return new UserAuthenticationService($parameterBag, $logger);
    }

    /**
     * @return UserAuthenticationService
     */
    private function getServiceWithoutLdap()
    {
        self::bootKernel();
        $parameterBag = new ParameterBag(['LDAP_URL' => 'null://localhost', 'VALID_REGISTRATION_EMAILS' => 'all']);
        $logger = self::$container->get(LoggerInterface::class);

        return new UserAuthenticationService($parameterBag, $logger);
    }

    /**
     * @return UserAuthenticationService
     */
    private function getServiceWithRegistrationEmail()
    {
        self::bootKernel();
        //ldapsearch -h ldap.forumsys.com -D "uid=tesla,dc=example,dc=com" -b "dc=example,dc=com" -w password "(uid=training)"
        $parameterBag = new ParameterBag(['LDAP_URL' => 'null://localhost', 'VALID_REGISTRATION_EMAILS' => 'one@example.com;two@example.com']);
        $logger = self::$container->get(LoggerInterface::class);

        return new UserAuthenticationService($parameterBag, $logger);
    }
}
