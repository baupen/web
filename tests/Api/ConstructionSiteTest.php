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
        $this->assertApiOperationUnsupported($client, '/api/construction_sites/'.$testConstructionSite->getId(), 'DELETE', 'PUT');
    }

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $testConstructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/construction_sites', 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/construction_sites/'.$testConstructionSite->getId(), 'GET');

        $this->loginApiAssociatedConstructionManager($client);
        $this->assertApiOperationForbidden($client, '/api/construction_sites', 'POST');
        $this->assertApiOperationForbidden($client, '/api/construction_sites/'.$testConstructionSite->getId(), 'GET');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $response = $this->assertApiGetOk($client, '/api/construction_sites');
        $this->assertApiResponseFieldSubset($response, 'name', 'streetAddress', 'postalCode', 'locality', 'imageUrl', 'constructionManagers', 'isDeleted', 'lastChangedAt', 'createdAt');
        $this->assertApiResponseFileIsDownloadable($client, $response, 'imageUrl');
    }

    public function testPostAndPatch()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerIri = $this->getIriFromItem($constructionManager);

        $sample = [
            'name' => 'New',
            'streetAddress' => 'Some Address',
            'postalCode' => 4123,
            'locality' => 'Allschwil',
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_BAD_REQUEST, $client, '/api/construction_sites', $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/construction_sites', $sample);

        $newConstructionSite = json_decode($response->getContent(), true);
        $this->assertApiCollectionContainsResponseItem($client, '/api/construction_sites', $response);

        $associatedConstructionManager = $this->loginApiAssociatedConstructionManager($client);
        $this->assertApiGetStatusCodeSame(Response::HTTP_FORBIDDEN, $client, $newConstructionSite['@id']);
        $this->assertApiPostStatusCodeSame(Response::HTTP_FORBIDDEN, $client, '/api/construction_sites', $sample);
        $this->assertApiPatchStatusCodeSame(Response::HTTP_FORBIDDEN, $client, $newConstructionSite['@id'], $sample);

        $patch = [
            'name' => 'New 2',
            'streetAddress' => 'New Address',
            'postalCode' => 1234,
            'locality' => 'Teerwil',
            'constructionManagers' => [
                $this->getIriFromItem($associatedConstructionManager),
            ],
        ];
        $this->loginApiConstructionManager($client);
        $this->assertApiPatchStatusCodeSame(Response::HTTP_OK, $client, $newConstructionSite['@id'], $patch);

        $this->loginApiAssociatedConstructionManager($client);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, $newConstructionSite['@id']);
        $this->assertApiPatchStatusCodeSame(Response::HTTP_OK, $client, $newConstructionSite['@id'], $sample);
    }

    public function testLastChangedAtFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteIri = $this->getIriFromItem($constructionSite);

        $this->assertApiCollectionFilterDateTime($client, '/api/construction_sites?', $constructionSiteIri, 'lastChangedAt', $constructionSite->getLastChangedAt());
    }
}
