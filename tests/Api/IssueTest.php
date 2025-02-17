<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Helper\DateTimeFormatter;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as StatusCode;

class IssueTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/issues?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/issues/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiOperationForbidden($client, '/api/issues', 'POST');
        $this->assertApiOperationForbidden($client, '/api/issues/'.$constructionSite->getIssues()[0]->getId(), 'GET', 'PATCH', 'DELETE');
    }

    public function testGet(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/issues?constructionSite='.$constructionSite->getId());
        $fields = ['number', 'description', 'deadline', 'wasAddedWithClient', 'isMarked', 'isDeleted', 'lastChangedAt'];
        $relationFields = ['craftsman', 'map', 'imageUrl', 'mapRenderUrl'];
        $statusFields = ['createdAt', 'createdBy', 'registeredAt', 'registeredBy', 'resolvedAt', 'resolvedBy', 'closedAt', 'closedBy'];
        $positionFields = ['positionX', 'positionY', 'positionZoomScale'];
        $allFields = array_merge($fields, $relationFields, $statusFields, $positionFields);

        $this->assertApiResponseFieldSubset($response, ...$allFields);
        $this->assertApiResponseFileIsDownloadable($client, $response, 'imageUrl');
        $this->assertApiResponseFileIsDownloadable($client, $response, 'mapRenderUrl');
    }

    public function testPostPatchAndDelete(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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
            'createdAt' => (new \DateTime('tomorrow'))->format('c'),
            'createdBy' => $otherConstructionManagerId,
        ];
        $this->assertApiPatchPayloadIgnored($client, $issueId, $writeProtected);

        $otherCraftsman = $this->getTestConstructionSite()->getCraftsmen()[1];
        $otherCraftsmanId = $this->getIriFromItem($otherCraftsman);
        $update = [
            'craftsman' => $otherCraftsmanId,
            'map' => $otherMapId,

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

    public function testPostNumberAssignment(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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
        $this->assertSame(1, $number);

        $response = $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $affiliation);
        $number = json_decode($response->getContent(), true)['number'];
        $this->assertSame(2, $number);
    }

    public function testIsDeletedFilter(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $issue = $constructionSite->getIssues()[0];
        $this->assertFalse($issue->getIsDeleted(), 'ensure issue is not deleted, else the following tests will fail');

        $issueIri = $this->getIriFromItem($issue);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId(), $issueIri);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&isDeleted=false', $issueIri);
        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&isDeleted=true', $issueIri);
    }

    public function testStateFilter(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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

    public function testLastChangedOrder(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);
        $constructionSite = $this->getTestConstructionSite();

        $issue = $constructionSite->getIssues()[0];
        $issue->setDescription('Hi');
        sleep(1); // sleep one second to ensure lastChangedAt different
        $this->saveEntity($issue);
        $this->assertOrderAppliedFor('lastChangedAt', $client, $constructionSite);
    }

    public function testDeadlineNumberOrder(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);
        $constructionSite = $this->getTestConstructionSite();

        $counter = 1;
        foreach ($constructionSite->getIssues() as $issue) {
            $issue->setNumber($counter);
            $issue->setDeadline(new \DateTime('today + '.$counter++.' hours'));
        }
        $this->saveEntity(...$constructionSite->getIssues()->toArray());

        $this->assertOrderAppliedFor('deadline', $client, $constructionSite);
        $this->assertOrderAppliedFor('number', $client, $constructionSite);
    }

    public function testLastChangedAtFilter(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $issue = $constructionSite->getIssues()[0];
        $issueIri = $this->getIriFromItem($issue);

        $this->assertApiCollectionFilterDateTime($client, '/api/issues?constructionSite='.$constructionSite->getId().'&', $issueIri, 'lastChangedAt', $issue->getLastChangedAt());
    }

    public function testPositionMustBeFullySetOrNotAtAll(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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

    public function testRelationsOnSameConstructionSite(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);

        $basePayload = $this->getMinimalPostPayload($constructionManager);

        $otherConstructionSite = $this->getEmptyConstructionSite();
        $map = $this->addMap($otherConstructionSite);
        $craftsman = $this->addCraftsman($otherConstructionSite);

        $this->assertApiPostStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issues', array_merge($basePayload, ['map' => $this->getIriFromItem($map)]));
        $this->assertApiPostStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issues', array_merge($basePayload, ['craftsman' => $this->getIriFromItem($craftsman)]));
        $this->assertApiPostPayloadPersisted($client, '/api/issues', [], $basePayload);
    }

    public function testStatusMustBeFullySetOrNotAtAll(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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

    public function getMinimalPostPayload(ConstructionManager $constructionManager): array
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

    public function testAllFilters(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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

    public function testReport(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(StatusCode::HTTP_OK, $client, '/api/issues/report?constructionSite='.$constructionSite->getId(), 'application/pdf');
        $this->assertTrue(str_contains($response->getContent(), '/download'));
    }

    public function testRender(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $map = $constructionSite->getMaps()[0];

        $urlWithConstructionSite = '/api/issues/render.jpg?constructionSite='.$constructionSite->getId();
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, $urlWithConstructionSite, 'image/jpeg');

        $fullUrl = $urlWithConstructionSite.'&map='.$map->getId();
        $response = $this->assertApiGetOk($client, $fullUrl, 'image/jpeg');
        $this->assertInstanceOf(BinaryFileResponse::class, $response->getKernelResponse());

        $response = $client->request('GET', $fullUrl, ['headers' => ['X-EMPTY-RESPONSE-EXPECTED' => '', 'Accept' => 'image/jpeg']]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertNotInstanceOf(BinaryFileResponse::class, $response->getKernelResponse());
        $this->assertSame('', $response->getContent());
    }

    public function testStatistics(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getEmptyConstructionSite();
        $constructionManager = $this->getTestConstructionManager();
        $this->assignConstructionManager($constructionSite, $constructionManager);
        $craftsman = $this->addCraftsman($constructionSite);

        $newIssue = function () use ($constructionSite, $constructionManager): Issue {
            $issue = new Issue();

            $issue->setConstructionSite($constructionSite);
            $issue->setNumber(0);

            $issue->setCreatedAt(new \DateTime());
            $issue->setCreatedBy($constructionManager);

            return $issue;
        };

        $registerIssue = function (Issue $issue) use ($constructionManager): void {
            $issue->setRegisteredAt(new \DateTime());
            $issue->setRegisteredBy($constructionManager);
        };

        $resolveIssue = function (Issue $issue) use ($craftsman): void {
            $issue->setResolvedAt(new \DateTime());
            $issue->setResolvedBy($craftsman);
        };

        $closeIssue = function (Issue $issue) use ($constructionManager): void {
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

        $this->assertSame(1, $summary['newCount']);
        $this->assertSame(1, $summary['openCount']);
        $this->assertSame(1, $summary['inspectableCount']);
        $this->assertSame(2, $summary['closedCount']);
    }

    public function testGroup(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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
        foreach ($groups['hydra:member'] as $group) {
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

    public function testFeedEntries(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getEmptyConstructionSite();
        $constructionManager = $this->getTestConstructionManager();
        $this->assignConstructionManager($constructionSite, $constructionManager);
        $craftsman = $this->addCraftsman($constructionSite);

        $newIssue = function () use ($constructionSite, $constructionManager): Issue {
            $issue = new Issue();

            $issue->setConstructionSite($constructionSite);
            $issue->setNumber(0);

            $issue->setCreatedAt(new \DateTime('today - 1 month'));
            $issue->setCreatedBy($constructionManager);

            return $issue;
        };

        $newIssues = [];

        $issue = $newIssue(); // 1
        $issue->setRegisteredAt(new \DateTime('today'));
        $issue->setRegisteredBy($constructionManager);
        $newIssues[] = $issue;

        $issue = $newIssue();
        $issue->setRegisteredAt(new \DateTime('yesterday'));
        $issue->setRegisteredBy($constructionManager);
        $issue->setResolvedAt(new \DateTime('yesterday'));
        $issue->setResolvedBy($craftsman);
        $issue->setClosedAt(new \DateTime('yesterday'));
        $issue->setClosedBy($constructionManager);
        $newIssues[] = $issue;

        $issue = $newIssue(); // 3
        $issue->setClosedAt(new \DateTime('yesterday'));
        $issue->setClosedBy($constructionManager);
        $newIssues[] = $issue;

        $this->saveEntity(...$newIssues);

        $response = $this->assertApiGetOk($client, '/api/issues/feed_entries?constructionSite='.$constructionSite->getId());
        $feedEntries = json_decode($response->getContent(), true);

        $constructionManagerIri = $this->getIriFromItem($constructionManager);
        $craftsmanIri = $this->getIriFromItem($craftsman);

        $expectedCombinations = [
            [
                (new \DateTime('today'))->format(DateTimeFormatter::ISO_DATE_FORMAT),
                $constructionManagerIri,
                1,
                1,
            ],
            [
                (new \DateTime('yesterday'))->format(DateTimeFormatter::ISO_DATE_FORMAT),
                $constructionManagerIri,
                1,
                1,
            ],
            [
                (new \DateTime('yesterday'))->format(DateTimeFormatter::ISO_DATE_FORMAT),
                $craftsmanIri,
                2,
                1,
            ],
            [
                (new \DateTime('yesterday'))->format(DateTimeFormatter::ISO_DATE_FORMAT),
                $constructionManagerIri,
                3,
                2,
            ],
        ];

        foreach ($feedEntries['hydra:member'] as $feedEntry) {
            $foundCombinationIndex = null;
            $counter = count($expectedCombinations);
            for ($i = 0; $i < $counter; ++$i) {
                $expectedCombination = $expectedCombinations[$i];
                if ($expectedCombination[0] === $feedEntry['date']
                    && $expectedCombination[1] === $feedEntry['subject']
                    && $expectedCombination[2] === $feedEntry['type']
                    && $expectedCombination[3] === $feedEntry['count']) {
                    $foundCombinationIndex = $i;
                    break;
                }
            }

            $this->assertNotNull($foundCombinationIndex, 'not any of the expected combinations');
            unset($expectedCombinations[$foundCombinationIndex]);
            $expectedCombinations = array_values($expectedCombinations);
        }
    }

    public function testTimeseries(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getEmptyConstructionSite();
        $constructionManager = $this->getTestConstructionManager();
        $this->assignConstructionManager($constructionSite, $constructionManager);
        $craftsman = $this->addCraftsman($constructionSite);

        $newIssue = function () use ($constructionSite, $constructionManager): Issue {
            $issue = new Issue();

            $issue->setConstructionSite($constructionSite);
            $issue->setNumber(0);

            $issue->setCreatedAt(new \DateTime('today - 1 month'));
            $issue->setCreatedBy($constructionManager);

            return $issue;
        };

        $registerIssue = function (Issue $issue, int $daysInThePast) use ($constructionManager): void {
            $issue->setRegisteredAt(new \DateTime('today - '.$daysInThePast.' days + 1 minute'));
            $issue->setRegisteredBy($constructionManager);
        };

        $resolveIssue = function (Issue $issue, int $daysInThePast) use ($craftsman): void {
            $issue->setResolvedAt(new \DateTime('today - '.$daysInThePast.' days + 1 minute'));
            $issue->setResolvedBy($craftsman);
        };

        $closeIssue = function (Issue $issue, int $daysInThePast) use ($constructionManager): void {
            $issue->setClosedAt(new \DateTime('today - '.$daysInThePast.' days + 1 minute'));
            $issue->setClosedBy($constructionManager);
        };

        $newIssues = [];

        $issue = $newIssue(); // 1
        $newIssues[] = $issue;

        $issue = $newIssue(); // 2
        $registerIssue($issue, 1);
        $newIssues[] = $issue;

        $issue = $newIssue(); // 3
        $registerIssue($issue, 2);
        $resolveIssue($issue, 1);
        $newIssues[] = $issue;

        $issue = $newIssue(); // 4
        $registerIssue($issue, 3);
        $resolveIssue($issue, 2);
        $closeIssue($issue, 1);
        $newIssues[] = $issue;

        $issue = $newIssue(); // 5
        $registerIssue($issue, 4);
        $closeIssue($issue, 1);
        $newIssues[] = $issue;
        $this->saveEntity(...$newIssues);

        $response = $this->assertApiGetOk($client, '/api/issues/timeseries?constructionSite='.$constructionSite->getId());
        $summaries = json_decode($response->getContent(), true);

        $members = $summaries['hydra:member'];
        $tomorrowEntry = $members[count($members) - 1];
        $todayEntry = $members[count($members) - 2];
        $yesterdayEntry = $members[count($members) - 3];
        $dayBeforeYesterdayEntry = $members[count($members) - 4];

        $this->assertSame((new \DateTime('tomorrow'))->format(DateTimeFormatter::ISO_DATE_FORMAT), $tomorrowEntry['date']);

        $this->assertSame((new \DateTime('today'))->format(DateTimeFormatter::ISO_DATE_FORMAT), $todayEntry['date']);
        $this->assertSame(1, $todayEntry['openCount']); // 2
        $this->assertSame(1, $todayEntry['inspectableCount']); // 3
        $this->assertSame(2, $todayEntry['closedCount']); // 5 #4

        $this->assertSame((new \DateTime('yesterday'))->format(DateTimeFormatter::ISO_DATE_FORMAT), $yesterdayEntry['date']);
        $this->assertSame(2, $yesterdayEntry['openCount']); // 5 #3
        $this->assertSame(1, $yesterdayEntry['inspectableCount']); // 4
        $this->assertSame(0, $yesterdayEntry['closedCount']);

        $this->assertSame(2, $dayBeforeYesterdayEntry['openCount']); // 5 #4
        $this->assertSame(0, $dayBeforeYesterdayEntry['inspectableCount']);
        $this->assertSame(0, $dayBeforeYesterdayEntry['closedCount']);
    }

    private function assertOrderAppliedFor(string $entry, Client $client, ConstructionSite $constructionSite): void
    {
        $url = '/api/issues?constructionSite='.$constructionSite->getId().'&order['.$entry.']=';

        $ascCollectionResponse = $this->assertApiGetOk($client, $url.'asc');
        $ascCollection = json_decode($ascCollectionResponse->getContent(), true);

        $descCollectionResponse = $this->assertApiGetOk($client, $url.'desc');
        $descCollection = json_decode($descCollectionResponse->getContent(), true);

        $reversedDescEntries = array_reverse($descCollection['hydra:member']);
        $this->assertEquals($ascCollection['hydra:member'], $reversedDescEntries, 'filter '.$entry.' has not been applied');
    }
}
