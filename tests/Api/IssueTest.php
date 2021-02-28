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
use App\Entity\Issue;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiOperationForbidden($client, '/api/issues', 'POST');
        $this->assertApiOperationForbidden($client, '/api/issues/'.$constructionSite->getIssues()[0]->getId(), 'GET', 'PATCH', 'DELETE');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/issues?constructionSite='.$constructionSite->getId());
        $fields = ['number', 'description', 'deadline', 'wasAddedWithClient', 'isMarked', 'isDeleted', 'lastChangedAt'];
        $relationFields = ['craftsman', 'map', 'imageUrl'];
        $statusFields = ['createdAt', 'createdBy', 'registeredAt', 'registeredBy', 'resolvedAt', 'resolvedBy', 'closedAt', 'closedBy'];
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

        $craftsman = $this->getTestConstructionSite()->getCraftsmen()[0];
        $craftsmanId = $this->getIriFromItem($craftsman);
        $optionalProperties = [
            'craftsman' => $craftsmanId,

            'description' => 'hello world',
            'wasAddedWithClient' => true,
            'isMarked' => true,
            'deadline' => (new \DateTime('today'))->format('c'),

            'positionX' => 0.5,
            'positionY' => 0.6,
            'positionZoomScale' => 0.7,

            'registeredAt' => (new \DateTime('today + 1 day'))->format('c'),
            'registeredBy' => $constructionManagerId,
            'resolvedAt' => (new \DateTime('today + 2 day'))->format('c'),
            'resolvedBy' => $craftsmanId,
            'closedAt' => (new \DateTime('today + 3 day'))->format('c'),
            'closedBy' => $constructionManagerId,
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issues', $sample, $affiliation);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/issues', $affiliation, $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/issues', array_merge($sample, $optionalProperties), $affiliation);
        $this->assertApiCollectionContainsResponseItem($client, '/api/issues?constructionSite='.$constructionSite->getId(), $response);
        $issueId = json_decode($response->getContent(), true)['@id'];

        $otherMap = $this->getTestConstructionSite()->getMaps()[1];
        $otherMapId = $this->getIriFromItem($otherMap);
        $otherConstructionManager = $this->getTestAssociatedConstructionManager();
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
            'deadline' => (new \DateTime('yesterday'))->format('c'),

            'positionX' => 0.6,
            'positionY' => 0.7,
            'positionZoomScale' => 0.8,

            'registeredAt' => (new \DateTime('yesterday + 1 day'))->format('c'),
            'registeredBy' => $otherConstructionManagerId,
            'resolvedAt' => (new \DateTime('yesterday + 2 day'))->format('c'),
            'resolvedBy' => $otherCraftsmanId,
            'closedAt' => (new \DateTime('yesterday + 3 day'))->format('c'),
            'closedBy' => $otherConstructionManagerId,
        ];
        $response = $this->assertApiPatchPayloadPersisted($client, $issueId, $update);
        $this->assertApiCollectionContainsResponseItem($client, '/api/issues?constructionSite='.$constructionSite->getId(), $response);

        $this->assertApiDeleteOk($client, $issueId);
        $this->assertApiCollectionContainsResponseItemDeleted($client, '/api/issues?constructionSite='.$constructionSite->getId(), $response);
    }

    public function testPostNumberAssignment()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);

        $constructionSite = $this->getEmptyConstructionSite();
        $this->assignConstructionManager($constructionSite, $constructionManager);
        $map = $this->addMap($constructionSite);

        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $mapId = $this->getIriFromItem($map);
        $constructionManagerId = $this->getIriFromItem($constructionManager);

        $affiliation = ['constructionSite' => $constructionSiteId];
        $payload = ['map' => $mapId, 'createdBy' => $constructionManagerId, 'createdAt' => (new \DateTime())->format('c')];

        $response = $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $affiliation);
        $number = json_decode($response->getContent(), true)['number'];
        $this->assertEquals(1, $number);

        $response = $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $affiliation);
        $number = json_decode($response->getContent(), true)['number'];
        $this->assertEquals(2, $number);
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

        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=1', $issueId);

        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=2', $issueId);
        $this->assertApiPatchOk($client, $issueId, ['registeredBy' => $constructionManagerId, 'registeredAt' => $time]);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=2', $issueId);

        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=1', $issueId);

        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=4', $issueId);
        $this->assertApiPatchOk($client, $issueId, ['resolvedBy' => $craftsmanId, 'resolvedAt' => $time]);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=4', $issueId);

        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=8', $issueId);
        $this->assertApiPatchOk($client, $issueId, ['closedBy' => $constructionManagerId, 'closedAt' => $time]);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&state=8', $issueId);
    }

    public function testOrder()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);
        $constructionSite = $this->getTestConstructionSite();

        $issue = $constructionSite->getIssues()[0];
        $issue->setDeadline(new \DateTime());
        $this->saveEntity($issue);

        $order = ['lastChangedAt', 'deadline', 'number'];
        foreach ($order as $entry) {
            $url = '/api/issues?constructionSite='.$constructionSite->getId().'&order['.$entry.']=';

            $ascCollectionResponse = $this->assertApiGetOk($client, $url.'asc');
            $ascCollection = json_decode($ascCollectionResponse->getContent(), true);

            $descCollectionResponse = $this->assertApiGetOk($client, $url.'desc');
            $descCollection = json_decode($descCollectionResponse->getContent(), true);

            $reversedDescEntries = array_reverse($descCollection['hydra:member']);
            $this->assertEquals($ascCollection['hydra:member'], $reversedDescEntries, 'filter '.$entry.' has not been applied');
        }
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

        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issues', $payload, $basePayload);
        $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $basePayload);
    }

    public function testRelationsOnSameConstructionSite()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);

        $basePayload = $this->getMinimalPostPayload($constructionManager);

        $otherConstructionSite = $this->getEmptyConstructionSite();
        $map = $this->addMap($otherConstructionSite);
        $craftsman = $this->addCraftsman($otherConstructionSite);

        $this->assertApiPostStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issues', array_merge($basePayload, ['map' => $this->getIriFromItem($map)]));
        $this->assertApiPostStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issues', array_merge($basePayload, ['craftsman' => $this->getIriFromItem($craftsman)]));
        $this->assertApiPostPayloadPersisted($client, '/api/issues', [], $basePayload);
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

        $payload = ['registeredBy' => $constructionManagerId, 'registeredAt' => $time];
        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issues', $payload, $basePayload);
        $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $basePayload);

        $payload = ['resolvedBy' => $craftsmanId, 'resolvedAt' => $time];
        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issues', $payload, $basePayload);
        $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $basePayload);

        $payload = ['closedBy' => $constructionManagerId, 'closedAt' => $time];
        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issues', $payload, $basePayload);
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
            'deadline' => (new \DateTime('today'))->format('c'),

            'createdBy' => $constructionManagerId,
            'createdAt' => (new \DateTime())->format('c'),
            'registeredAt' => (new \DateTime('today + 1 day'))->format('c'),
            'registeredBy' => $constructionManagerId,
            'resolvedAt' => (new \DateTime('today + 2 day'))->format('c'),
            'resolvedBy' => $craftsmanId,
            'closedAt' => (new \DateTime('today + 3 day'))->format('c'),
            'closedBy' => $constructionManagerId,

            'state' => 1,
        ];

        $response = $this->assertApiPostStatusCodeSame(Response::HTTP_CREATED, $client, '/api/issues', $sample);
        $this->assertApiCollectionContainsResponseItem($client, '/api/issues?constructionSite='.$constructionSite->getId(), $response);
        $issueIri = json_decode($response->getContent(), true)['@id'];

        $collectionUrlPrefix = '/api/issues?constructionSite='.$constructionSite->getId().'&';

        $dateTimeProperties = ['createdAt', 'registeredAt', 'resolvedAt', 'closedAt', 'deadline'];
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

    public function testReport()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiGetOk($client, '/api/issues/report?constructionSite='.$constructionSite->getId());
    }

    public function testRender()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $map = $constructionSite->getMaps()[0];

        $urlWithConstructionSite = '/api/issues/render.jpg?constructionSite='.$constructionSite->getId();
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, $urlWithConstructionSite);

        $fullUrl = $urlWithConstructionSite.'&map='.$map->getId();
        $response = $this->assertApiGetOk($client, $fullUrl);
        $this->assertTrue($response->getKernelResponse() instanceof BinaryFileResponse);

        $response = $client->request('GET', $fullUrl, ['headers' => ['X-EMPTY-RESPONSE-EXPECTED' => '']]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertTrue(!$response->getKernelResponse() instanceof BinaryFileResponse);
        $this->assertTrue('' === $response->getContent());
    }

    public function testStatistics()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getEmptyConstructionSite();
        $constructionManager = $this->getTestConstructionManager();
        $this->assignConstructionManager($constructionSite, $constructionManager);
        $craftsman = $this->addCraftsman($constructionSite);

        $newIssue = function () use ($constructionSite, $constructionManager) {
            $issue = new Issue();

            $issue->setConstructionSite($constructionSite);
            $issue->setNumber(0);

            $issue->setCreatedAt(new \DateTime());
            $issue->setCreatedBy($constructionManager);

            return $issue;
        };

        $registerIssue = function (Issue $issue) use ($constructionManager) {
            $issue->setRegisteredAt(new \DateTime());
            $issue->setRegisteredBy($constructionManager);
        };

        $resolveIssue = function (Issue $issue) use ($craftsman) {
            $issue->setResolvedAt(new \DateTime());
            $issue->setResolvedBy($craftsman);
        };

        $closeIssue = function (Issue $issue) use ($constructionManager) {
            $issue->setClosedAt(new \DateTime());
            $issue->setClosedBy($constructionManager);
        };

        $newIssues = [];

        $issue = $newIssue();
        $newIssues[] = $issue;

        $issue = $newIssue();
        $registerIssue($issue);
        $newIssues[] = $issue;

        $issue = $newIssue();
        $registerIssue($issue);
        $resolveIssue($issue);
        $newIssues[] = $issue;

        $issue = $newIssue();
        $registerIssue($issue);
        $resolveIssue($issue);
        $closeIssue($issue);
        $newIssues[] = $issue;

        $issue = $newIssue();
        $registerIssue($issue);
        $closeIssue($issue);
        $newIssues[] = $issue;
        $this->saveEntity(...$newIssues);

        $response = $this->assertApiGetOk($client, '/api/issues/summary?constructionSite='.$constructionSite->getId());
        $summary = json_decode($response->getContent(), true);

        $this->assertEquals(1, $summary['newCount']);
        $this->assertEquals(1, $summary['openCount']);
        $this->assertEquals(1, $summary['inspectableCount']);
        $this->assertEquals(2, $summary['closedCount']);
    }

    public function testGroup()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues/group?constructionSite='.$constructionSite->getId());
        $response = $this->assertApiGetOk($client, '/api/issues/group?constructionSite='.$constructionSite->getId().'&group=map');

        $groups = json_decode($response->getContent(), true);

        $mapLookupByIri = [];
        foreach ($constructionSite->getMaps() as $map) {
            $mapLookupByIri['/api/maps/'.$map->getId()] = $map;
        }

        // each group count should match with map issue count
        foreach ($groups as $group) {
            $mapIri = $group['entity'];

            $map = $mapLookupByIri[$mapIri];
            $this->assertSame($map->getIssues()->count(), $group['count']);

            unset($mapLookupByIri[$mapIri]);
        }

        // all other maps not contained in group should have no issues assigned
        foreach ($mapLookupByIri as $map) {
            $this->assertEmpty($map->getIssues());
        }
    }

    public function testFeed()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiGetOk($client, '/api/issues/feed_entries?constructionSite='.$constructionSite->getId());
    }
}
