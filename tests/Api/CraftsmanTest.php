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
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;

class CraftsmanTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), 'GET');

        // $testConstructionSite = $this->getTestConstructionSite();
        // $this->assertApiOperationNotAuthorized($client, '/api/craftsmen/'.$testConstructionSite->getId(), 'GET', 'DELETE', 'PUT', 'PATCH');

        // TODO: test for POST, 'GET' 'DELETE', 'PUT', 'PATCH'
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetResponseCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetResponseCodeSame(Response::HTTP_OK, $client, '/api/craftsmen?constructionSite='.$constructionSite->getId());
        $this->assertContainsOnlyListedFields($response, 'email', 'contactName', 'company', 'trade');
    }

    public function ignoredTestPost()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->findIriBy(ConstructionSite::class, ['id' => $constructionSite->getId()]);

        $sample = [
            'name' => 'New',
            'streetAddress' => 'Some Address',
            'postalCode' => 4123,
            'locality' => 'Allschwil',
            'constructionSiteId' => $constructionSiteId,
        ];

        $this->assertApiPostPayloadMinimal($client, '/api/craftsmen', $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/craftsmen', $sample);
        $newCraftsman = json_decode($response->getContent(), true);

        $client->request('GET', '/api/craftsmen?construction_site_id='.$constructionSite->getId(), [
            'headers' => ['Content-Type' => 'application/json'],
        ]);
        $this->assertJsonContains($newCraftsman);
    }
}
