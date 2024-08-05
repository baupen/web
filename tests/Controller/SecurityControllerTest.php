<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller;

use App\Entity\ConstructionManager;
use App\Tests\Traits\AssertAuthenticationTrait;
use App\Tests\Traits\AssertEmailTrait;
use App\Tests\Traits\FixturesTrait;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AssertEmailTrait;
    use AssertAuthenticationTrait;

    public function testCanRegister(): void
    {
        $client = $this->createClient();
        $this->loadFixturesBrowser($client);

        $email = 'f@baupen.ch';
        $password = 'asdf1234';

        $this->assertNotAuthenticated($client);
        $this->register($client, $email);
        $this->assertNotAuthenticated($client);

        $this->registerConfirm($client, $email, $password);
        $this->assertAuthenticated($client);

        $this->logout($client);
        $this->assertNotAuthenticated($client);

        $this->login($client, $email, $password);
        $this->assertAuthenticated($client);

        $this->logout($client);
        $this->assertNotAuthenticated($client);

        $this->recover($client, $email);
        $this->assertNotAuthenticated($client);

        $this->recoverConfirm($client, $email, $password);
        $this->assertAuthenticated($client);
    }

    private function login(KernelBrowser $client, string $email, string $password): void
    {
        $crawler = $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('login_submit')->form();
        $form['login[email]'] = $email;
        $form['login[password]'] = $password;

        $client->submit($form);
        $this->assertResponseRedirects();
    }

    private function logout(KernelBrowser $client): void
    {
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/logout');
        $this->assertResponseRedirects();
    }

    private function register(KernelBrowser $client, string $email): void
    {
        $crawler = $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/register');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('only_email_submit')->form();
        $form['only_email[email]'] = $email;

        $client->submit($form);
        $this->assertResponseRedirects();
        $authenticationHash = $this->getAuthenticationHash($email);
        $this->assertSingleEmailSentWithBodyContains($authenticationHash);

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('erfolgreich', $client->getResponse()->getContent()); // alert to user
    }

    private function registerConfirm(KernelBrowser $client, string $email, string $password): void
    {
        $authenticationHash = $this->getAuthenticationHash($email);

        $crawler = $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/register/confirm/'.$authenticationHash);
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('register_confirm_submit')->form();
        $form['register_confirm[profile][givenName]'] = 'Florian';
        $form['register_confirm[profile][familyName]'] = 'Moser';
        $form['register_confirm[profile][phone]'] = '0781234567';
        $form['register_confirm[password][plainPassword]'] = $password;
        $form['register_confirm[password][repeatPlainPassword]'] = $password;
        $client->submit($form);
        $this->assertSingleEmailSentWithBodyContains('https://apps.apple.com/ch/app/mangel-io/id1414077195'); // iOS download link

        $this->assertResponseRedirects('/help/welcome');
        $client->followRedirect();
        $this->assertStringContainsString('eingerichtet', $client->getResponse()->getContent()); // alert to user
    }

    private function recover(KernelBrowser $client, string $email): void
    {
        $crawler = $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/recover');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('only_email_submit')->form();
        $form['only_email[email]'] = $email;

        $client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('versandt', $client->getResponse()->getContent()); // alert to user

        $authenticationHash = $this->getAuthenticationHash($email);
        $this->assertSingleEmailSentWithBodyContains($authenticationHash);
    }

    private function recoverConfirm(KernelBrowser $client, string $email, string $password): void
    {
        $authenticationHash = $this->getAuthenticationHash($email);

        $crawler = $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/recover/confirm/'.$authenticationHash);
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('set_password_submit')->form();
        $form['set_password[plainPassword]'] = $password;
        $form['set_password[repeatPlainPassword]'] = $password;
        $client->submit($form);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertStringContainsString('gesetzt', $client->getResponse()->getContent()); // alert to user
    }

    private function getAuthenticationHash(string $email): ?string
    {
        $registry = static::$container->get(ManagerRegistry::class);
        $repository = $registry->getRepository(ConstructionManager::class);
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $repository->findOneBy(['email' => $email]);

        return $constructionManager->getAuthenticationHash();
    }
}
