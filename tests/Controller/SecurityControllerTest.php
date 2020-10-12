<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller;

use App\Entity\ConstructionManager;
use Doctrine\Persistence\ManagerRegistry;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testCanRegister()
    {
        $client = $this->createClient();

        $this->loadFixtures();

        $this->register($client, 'f@mangel.io');
        $this->assertStringContainsString('erfolgreich', $client->getResponse()->getContent());

        $this->confirmRegister($client, 'f@mangel.io');
        $this->assertStringContainsString('eingerichtet', $client->getResponse()->getContent());
    }

    private function confirmRegister(KernelBrowser $client, string $email)
    {
        $authenticationHash = $this->getAuthenticationHash($email);

        $crawler = $client->request('GET', '/register/confirm/'.$authenticationHash);
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('register_confirm_submit')->form();
        $form['register_confirm[profile][givenName]'] = 'Florian';
        $form['register_confirm[profile][familyName]'] = 'Moser';
        $form['register_confirm[profile][phone]'] = '0781234567';
        $form['register_confirm[password][plainPassword]'] = 'asdf1234';
        $form['register_confirm[password][repeatPlainPassword]'] = 'asdf1234';
        $client->submit($form);

        $this->assertResponseRedirects('/help/welcome');
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        return $crawler;
    }

    private function register(KernelBrowser $client, string $email): Crawler
    {
        $crawler = $client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('only_email_submit')->form();
        $form['only_email[email]'] = $email;
        $crawler = $client->submit($form);
        $this->assertResponseIsSuccessful();

        return $crawler;
    }

    private function getAuthenticationHash(string $email)
    {
        $registry = static::$container->get(ManagerRegistry::class);
        $repository = $registry->getRepository(ConstructionManager::class);
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $repository->findOneBy(['email' => $email]);

        return $constructionManager->getAuthenticationHash();
    }
}
