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
use App\Entity\ConstructionManager;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;

class IssueTest extends ApiTestCase
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
        $this->assertApiOperationNotAuthorized($client, '/api/issues?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/issues/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');

        $this->loginApiConstructionManagerExternal($client);
        $this->assertApiOperationForbidden($client, '/api/issues?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationForbidden($client, '/api/issues/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/issues?constructionSite='.$constructionSite->getId());
        $fields = ['number', 'description', 'responseLimit', 'wasAddedWithClient', 'isMarked', 'isDeleted', 'lastChangedAt'];
        $relationFields = ['craftsman', 'map', 'mapFile', 'imageUrl'];
        $statusFields = ['createdAt', 'createdBy', 'registeredAt', 'registrationBy', 'respondedAt', 'responseBy', 'reviewedAt', 'reviewBy'];
        $positionFields = ['positionX', 'positionY', 'positionZoomScale'];
        $allFields = array_merge($fields, $relationFields, $statusFields, $positionFields);

        $this->assertApiResponseFieldSubset($response, ...$allFields);
        $this->assertApiResponseFileIsDownloadable($client, $response, 'imageUrl');
    }

    public function testPostPatchAndDelete()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerId = $this->getIriFromItem($constructionManager);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = [
            'constructionSite' => $constructionSiteId,
        ];

        $map = $constructionSite->getMaps()[0];
        $mapId = $this->getIriFromItem($map);
        $sample = [
            'map' => $mapId,

            'createdBy' => $constructionManagerId,
            'createdAt' => (new \DateTime())->format('c'),
        ];

        $mapFileId = $this->getIriFromItem($map->getFile());
        $craftsman = $this->getTestConstructionSite()->getCraftsmen()[0];
        $craftsmanId = $this->getIriFromItem($craftsman);
        $optionalProperties = [
            'mapFile' => $mapFileId,
            'craftsman' => $craftsmanId,

            'description' => 'hello world',
            'wasAddedWithClient' => true,
            'isMarked' => true,
            'responseLimit' => (new \DateTime('today'))->format('c'),

            'positionX' => 0.5,
            'positionY' => 0.6,
            'positionZoomScale' => 0.7,

            'registeredAt' => (new \DateTime('today + 1 day'))->format('c'),
            'registrationBy' => $constructionManagerId,
            'respondedAt' => (new \DateTime('today + 2 day'))->format('c'),
            'responseBy' => $craftsmanId,
            'reviewedAt' => (new \DateTime('today + 3 day'))->format('c'),
            'reviewBy' => $constructionManagerId,
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_BAD_REQUEST, $client, '/api/issues', $sample, $affiliation);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/issues', $affiliation, $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/issues', array_merge($sample, $optionalProperties), $affiliation);
        $this->assertApiCollectionContainsResponseItem($client, '/api/issues?constructionSite='.$constructionSite->getId(), $response);
        $issueId = json_decode($response->getContent(), true)['@id'];

        $otherMap = $this->getTestConstructionSite()->getMaps()[1];
        $otherMapId = $this->getIriFromItem($otherMap);
        $otherConstructionManager = $this->getTestConstructionSite()->getConstructionManagers()[1];
        $otherConstructionManagerId = $this->getIriFromItem($otherConstructionManager);

        $emptyConstructionSite = $this->getEmptyConstructionSite();
        $emptyConstructionSiteId = $this->getIriFromItem($emptyConstructionSite);
        $writeProtected = [
            'constructionSite' => $emptyConstructionSiteId,
            'map' => $otherMapId,
            'createdAt' => (new \DateTime('tomorrow'))->format('c'),
            'createdBy' => $otherConstructionManagerId,
        ];
        $this->assertApiPatchPayloadIgnored($client, $issueId, $writeProtected);

        $otherCraftsman = $this->getTestConstructionSite()->getCraftsmen()[1];
        $otherCraftsmanId = $this->getIriFromItem($otherCraftsman);
        $update = [
            'craftsman' => $otherCraftsmanId,

            'description' => 'hello world 2',
            'wasAddedWithClient' => false,
            'isMarked' => false,
            'responseLimit' => (new \DateTime('yesterday'))->format('c'),

            'positionX' => 0.6,
            'positionY' => 0.7,
            'positionZoomScale' => 0.8,

            'registeredAt' => (new \DateTime('yesterday + 1 day'))->format('c'),
            'registrationBy' => $otherConstructionManagerId,
            'respondedAt' => (new \DateTime('yesterday + 2 day'))->format('c'),
            'responseBy' => $otherCraftsmanId,
            'reviewedAt' => (new \DateTime('yesterday + 3 day'))->format('c'),
            'reviewBy' => $otherConstructionManagerId,
        ];
        $response = $this->assertApiPatchPayloadPersisted($client, $issueId, $update);
        $this->assertApiCollectionContainsResponseItem($client, '/api/issues?constructionSite='.$constructionSite->getId(), $response);

        $this->assertApiDeleteOk($client, $issueId);
        $this->assertApiCollectionContainsResponseItemDeleted($client, '/api/issues?constructionSite='.$constructionSite->getId(), $response);
    }

    public function testIsDeletedFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $issue = $constructionSite->getIssues()[0];
        $this->assertFalse($issue->getIsDeleted(), 'ensure issue is not deleted, else the following tests will fail');

        $issueIri = $this->getIriFromItem($issue);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId(), $issueIri);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&isDeleted=false', $issueIri);
        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&isDeleted=true', $issueIri);
    }

    public function testStateFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerId = $this->getIriFromItem($constructionManager);
        $basePayload = $this->getMinimalPostPayload($constructionManager);

        $constructionSite = $this->getTestConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsmanId = $this->getIriFromItem($craftsman);
        $time = (new \DateTime('today'))->format('c');

        $response = $this->assertApiPostPayloadPersisted($client, '/api/issues', [], $basePayload);
        $issueId = json_decode($response->getContent(), true)['@id'];

        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=1', $issueId);
        $this->assertApiPatchOk($client, $issueId, ['registrationBy' => $constructionManagerId, 'registeredAt' => $time]);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=1', $issueId);

        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=4', $issueId);
        $this->assertApiPatchOk($client, $issueId, ['responseBy' => $craftsmanId, 'respondedAt' => $time]);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=4', $issueId);

        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=8', $issueId);
        $this->assertApiPatchOk($client, $issueId, ['reviewBy' => $constructionManagerId, 'reviewedAt' => $time]);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=8', $issueId);
    }

    public function testLastChangedAtFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $issue = $constructionSite->getIssues()[0];
        $issueIri = $this->getIriFromItem($issue);

        $this->assertApiCollectionFilterDateTime($client, '/api/issues?constructionSite='.$constructionSite->getId().'&', $issueIri, 'lastChangedAt', $issue->getLastChangedAt());
    }

    public function testPositionMustBeFullySetOrNotAtAll()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);

        $basePayload = $this->getMinimalPostPayload($constructionManager);

        $payload = [
            'positionX' => 0.5,
            'positionY' => 0.6,
            'positionZoomScale' => 0.7,
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_BAD_REQUEST, $client, '/api/issues', $payload, $basePayload);
        $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $basePayload);
    }

    public function testStatusMustBeFullySetOrNotAtAll()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerId = $this->getIriFromItem($constructionManager);
        $basePayload = $this->getMinimalPostPayload($constructionManager);

        $constructionSite = $this->getTestConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsmanId = $this->getIriFromItem($craftsman);
        $time = (new \DateTime('today'))->format('c');

        $payload = ['registrationBy' => $constructionManagerId, 'registeredAt' => $time];
        $this->assertApiPostPayloadMinimal(Response::HTTP_BAD_REQUEST, $client, '/api/issues', $payload, $basePayload);
        $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $basePayload);

        $payload = ['responseBy' => $craftsmanId, 'respondedAt' => $time];
        $this->assertApiPostPayloadMinimal(Response::HTTP_BAD_REQUEST, $client, '/api/issues', $payload, $basePayload);
        $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $basePayload);

        $payload = ['reviewBy' => $constructionManagerId, 'reviewedAt' => $time];
        $this->assertApiPostPayloadMinimal(Response::HTTP_BAD_REQUEST, $client, '/api/issues', $payload, $basePayload);
        $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $basePayload);
    }

    private function getMinimalPostPayload(ConstructionManager $constructionManager)
    {
        $constructionManagerId = $this->getIriFromItem($constructionManager);
        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $map = $constructionSite->getMaps()[0];
        $mapId = $this->getIriFromItem($map);

        return [
            'constructionSite' => $constructionSiteId,
            'map' => $mapId,

            'createdBy' => $constructionManagerId,
            'createdAt' => (new \DateTime())->format('c'),
        ];
    }

    public function testAllFilters()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerId = $this->getIriFromItem($constructionManager);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $map = $constructionSite->getMaps()[0];
        $mapId = $this->getIriFromItem($map);
        $craftsman = $this->getTestConstructionSite()->getCraftsmen()[0];
        $craftsmanId = $this->getIriFromItem($craftsman);
        $sample = [
            'constructionSite' => $constructionSiteId,
            'map' => $mapId,
            'craftsman' => $craftsmanId,

            'description' => 'hello world',
            'wasAddedWithClient' => true,
            'isMarked' => true,
            'responseLimit' => (new \DateTime('today'))->format('c'),

            'createdBy' => $constructionManagerId,
            'createdAt' => (new \DateTime())->format('c'),
            'registeredAt' => (new \DateTime('today + 1 day'))->format('c'),
            'registrationBy' => $constructionManagerId,
            'respondedAt' => (new \DateTime('today + 2 day'))->format('c'),
            'responseBy' => $craftsmanId,
            'reviewedAt' => (new \DateTime('today + 3 day'))->format('c'),
            'reviewBy' => $constructionManagerId,

            'state' => 1,
        ];

        $response = $this->assertApiPostStatusCodeSame(Response::HTTP_CREATED, $client, '/api/issues', $sample);
        $this->assertApiCollectionContainsResponseItem($client, '/api/issues?constructionSite='.$constructionSite->getId(), $response);
        $issueIri = json_decode($response->getContent(), true)['@id'];

        $collectionUrlPrefix = '/api/issues?constructionSite='.$constructionSite->getId().'&';

        $dateTimeProperties = ['createdAt', 'registeredAt', 'respondedAt', 'reviewedAt', 'responseLimit'];
        foreach ($dateTimeProperties as $dateTimeProperty) {
            $this->assertApiCollectionFilterDateTime($client, $collectionUrlPrefix, $issueIri, $dateTimeProperty, new \DateTime($sample[$dateTimeProperty]));
        }

        $boolProperties = ['wasAddedWithClient', 'isMarked'];
        foreach ($boolProperties as $boolProperty) {
            $this->assertApiCollectionFilterBoolean($client, $collectionUrlPrefix, $issueIri, $boolProperty, $sample[$boolProperty]);
        }

        $this->assertApiCollectionFilterSearchPartial($client, $collectionUrlPrefix, $issueIri, 'description', $sample['description']);

        $this->assertApiCollectionFilterSearchExact($client, $collectionUrlPrefix, $issueIri, 'map', $sample['map']);
        $this->assertApiCollectionFilterSearchExact($client, $collectionUrlPrefix, $issueIri, 'craftsman', $sample['craftsman']);
    }

    public function ignoredTestDownloadReport()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiGetOk($client, '/api/issues/report?constructionSite='.$constructionSite->getId());
    }
}
