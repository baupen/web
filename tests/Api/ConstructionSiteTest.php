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
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;

class ConstructionSiteTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testInvalidMethods()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $testConstructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationUnsupported($client, '/api/construction_sites/'.$testConstructionSite->getId(), 'DELETE', 'PUT', 'PATCH');
    }

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $this->assertApiOperationNotAuthorized($client, '/api/construction_sites', 'GET', 'POST');

        $testConstructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/construction_sites/'.$testConstructionSite->getId(), 'GET');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $response = $client->request('GET', '/api/construction_sites', [
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertContainsOnlyListedFields($response, 'name', 'streetAddress', 'postalCode', 'locality', 'imageUrl', 'constructionManagers');
        $this->assertApiFileUrlIsDownloadable($client, $response, 'imageUrl');
    }

    public function testPost()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);

        $sample = [
            'name' => 'New',
            'streetAddress' => 'Some Address',
            'postalCode' => 4123,
            'locality' => 'Allschwil',
        ];

        $this->assertApiPostPayloadMinimal($client, '/api/construction_sites', $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/construction_sites', $sample);

        $newConstructionSite = json_decode($response->getContent(), true);
        $this->assertResponseIsSuccessful();
        $this->assertContains('/api/construction_managers/'.$constructionManager->getId(), $newConstructionSite['constructionManagers']);

        $this->loginApiConstructionManagerExternal($client);
        $this->assertApiGetResponseCodeSame(Response::HTTP_FORBIDDEN, $client, $newConstructionSite['@id']);
        $this->assertApiPostResponseCodeSame(Response::HTTP_FORBIDDEN, $client, '/api/construction_sites', $sample);

        $this->loginApiConstructionManagerTrial($client);
        $this->assertApiGetResponseCodeSame(Response::HTTP_FORBIDDEN, $client, $newConstructionSite['@id']);
        $this->assertApiPostResponseCodeSame(Response::HTTP_FORBIDDEN, $client, '/api/construction_sites', $sample);
    }
}
