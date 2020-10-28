<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ConstructionSite;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\DataFixtures\TestUserFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use Doctrine\Persistence\ManagerRegistry;
use Liip\TestFixturesBundle\Test\FixturesTrait;

class ConstructionSiteTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;

    public function testInvalidMethods()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestUserFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiTestUser($client);

        $testConstructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationUnsupported($client, '/api/construction_sites/'.$testConstructionSite->getId(), 'DELETE', 'PUT', 'PATCH');
    }

    private function getTestConstructionSite(): ConstructionSite
    {
        $constructionSiteRepository = static::$container->get(ManagerRegistry::class)->getRepository(ConstructionSite::class);

        return $constructionSiteRepository->findOneByName(TestConstructionSiteFixtures::TEST_CONSTRUCTION_SITE_NAME);
    }

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestUserFixtures::class, TestConstructionSiteFixtures::class]);

        $this->assertApiOperationNotAuthorized($client, '/api/construction_sites', 'GET', 'POST');

        $testConstructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/construction_sites/'.$testConstructionSite->getId(), 'GET');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestUserFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiTestUser($client);

        $response = $client->request('GET', '/api/construction_sites', [
            'headers' => ['Content-Type' => 'application/json'],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertContainsOnlyListedFields($response, 'name', 'streetAddress', 'postalCode', 'locality', 'image');
    }

    public function testPost()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestUserFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiTestUser($client);

        $sample = [
            'name' => 'New',
            'streetAddress' => 'Some Address',
            'postalCode' => 4123,
            'locality' => 'Allschwil',
        ];

        $this->assertApiPostFieldsRequired($client, '/api/construction_sites', $sample);
        $this->assertApiPostFieldsPersisted($client, '/api/construction_sites', $sample);
    }
}
