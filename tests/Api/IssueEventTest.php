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
use App\Entity\Craftsman;
use App\Enum\IssueEventTypes;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\DataFixtures\TestIssueEventFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Symfony\Component\HttpFoundation\Response;

class IssueEventTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestIssueEventFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/issue_events?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/issue_events/'.$constructionSite->getId(), 'GET', 'DELETE');
        $this->assertApiOperationUnsupported($client, '/api/issue_events/'.$constructionSite->getId(), 'PATCH');

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiOperationForbidden($client, '/api/issue_events', 'POST');
        $this->assertApiOperationForbidden($client, '/api/issue_events/'.$constructionSite->getIssueEvents()[0]->getId(), 'GET', 'DELETE');
        $this->assertApiOperationUnsupported($client, '/api/issue_events/'.$constructionSite->getId(), 'PATCH');
    }

    public function testGet(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestIssueEventFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issue_events');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/issue_events?constructionSite='.$constructionSite->getId());
        $this->assertApiResponseFieldSubset($response, 'root', 'type', 'payload', 'createdAt', 'createdBy', 'isDeleted');
    }

    public function testPostAndDelete(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestIssueEventFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerId = $this->getIriFromItem($constructionManager);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = [
            'constructionSite' => $constructionSiteId,
        ];

        $sample = [
            'root' => $constructionSiteId,
            'createdBy' => $constructionManagerId,
            'createdAt' => (new \DateTime())->format('c'),
        ];

        $optionalProperties = [
            'type' => IssueEventTypes::Text->value,
            'payload' => 'Hello World',
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/issue_events', $sample, $affiliation);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/issue_events', $affiliation, $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/issue_events', array_merge($sample, $optionalProperties), $affiliation);
        $this->assertApiCollectionContainsResponseItem($client, '/api/issue_events?constructionSite='.$constructionSite->getId(), $response);
        $issueEventId = json_decode($response->getContent(), true)['@id'];

        $this->assertApiDeleteOk($client, $issueEventId);
        $this->assertApiCollectionContainsResponseItemDeleted($client, '/api/issue_events?constructionSite='.$constructionSite->getId(), $response);
    }

    public function testIsDeletedFilter(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestIssueEventFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $issueEvent = $constructionSite->getIssueEvents()[0];
        $this->assertFalse($issueEvent->getIsDeleted(), 'ensure not deleted, else the following tests will fail');

        $issueEventIri = $this->getIriFromItem($issueEvent);
        $this->assertApiCollectionContainsIri($client, '/api/issue_events?constructionSite='.$constructionSite->getId(), $issueEventIri);
        $this->assertApiCollectionContainsIri($client, '/api/issue_events?constructionSite='.$constructionSite->getId().'&isDeleted=false', $issueEventIri);
        $this->assertApiCollectionNotContainsIri($client, '/api/issue_events?constructionSite='.$constructionSite->getId().'&isDeleted=true', $issueEventIri);
    }

    public function testCreatedAtFilter(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestIssueEventFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $issueEvent = $constructionSite->getIssueEvents()[0];
        $issueEventIri = $this->getIriFromItem($issueEvent);

        $this->assertApiCollectionFilterDateTime($client, '/api/issue_events?constructionSite='.$constructionSite->getId().'&', $issueEventIri, 'createdAt', $issueEvent->getCreatedAt());
    }

    public function testIssueStatusChangeCreatesEntries(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerId = $this->getIriFromItem($constructionManager);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $map = $constructionSite->getMaps()[0];
        $mapId = $this->getIriFromItem($map);
        $basePayload = [
            'constructionSite' => $constructionSiteId,
            'map' => $mapId,

            'createdBy' => $constructionManagerId,
            'createdAt' => (new \DateTime())->format('c'),
        ];

        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsmanId = $this->getIriFromItem($craftsman);
        $time = (new \DateTime())->format('c');

        // post initially; no status change
        $response = $this->assertApiPostPayloadPersisted($client, '/api/issues', [], $basePayload);
        $currentIssue = json_decode($response->getContent(), true);
        $issueId = substr($currentIssue['@id'], strrpos($currentIssue['@id'], '/') + 1);
        $this->assertNewIssueEvents($client, $constructionSite->getId(), $issueId, [[IssueEventTypes::StatusSet, 'CREATED']]);

        sleep(1); // to enforce order

        // register issue
        $payload = ['registeredBy' => $constructionManagerId, 'registeredAt' => $time];
        $response = $this->assertApiPatchOk($client, '/api/issues/'.$issueId, array_merge($currentIssue, $payload));
        $currentIssue = json_decode($response->getContent(), true);
        $this->assertNewIssueEvents($client, $constructionSite->getId(), $issueId, [[IssueEventTypes::StatusSet, 'REGISTERED']]);

        sleep(1); // to enforce order

        // change resolved by, hence expect corresponding log entry
        $payload = ['resolvedBy' => $craftsmanId, 'resolvedAt' => $time];
        $response = $this->assertApiPatchOk($client, '/api/issues/'.$issueId, array_merge($currentIssue, $payload));
        $currentIssue = json_decode($response->getContent(), true);
        $this->assertNewIssueEvents($client, $constructionSite->getId(), $issueId, [[IssueEventTypes::StatusSet, 'RESOLVED']]);

        sleep(1); // to enforce order

        $payload = ['closedBy' => $constructionManagerId, 'closedAt' => $time];
        $response = $this->assertApiPatchOk($client, '/api/issues/'.$issueId, array_merge($currentIssue, $payload));
        $currentIssue = json_decode($response->getContent(), true);
        $this->assertNewIssueEvents($client, $constructionSite->getId(), $issueId, [[IssueEventTypes::StatusSet, 'CLOSED']]);

        sleep(1); // to enforce order

        $time = (new \DateTime())->format('c');
        $payload = ['closedBy' => $constructionManagerId, 'closedAt' => $time];
        $response = $this->assertApiPatchOk($client, '/api/issues/'.$issueId, array_merge($currentIssue, $payload));
        $currentIssue = json_decode($response->getContent(), true);
        $this->assertNewIssueEvents($client, $constructionSite->getId(), $issueId, [[IssueEventTypes::StatusSet, 'CLOSED']]);

        sleep(1); // to enforce order

        $payload = ['resolvedBy' => null, 'resolvedAt' => null, 'closedBy' => null, 'closedAt' => null];
        $response = $this->assertApiPatchOk($client, '/api/issues/'.$issueId, array_merge($currentIssue, $payload));
        json_decode($response->getContent(), true);
        $this->assertNewIssueEvents($client, $constructionSite->getId(), $issueId, [[IssueEventTypes::StatusUnset, 'RESOLVED'], [IssueEventTypes::StatusUnset, 'CLOSED']]);
    }

    public function testCraftsmanEmailEntry(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0]; /** @var Craftsman $craftsman */
        $craftsmanId = $this->getIriFromItem($craftsman);

        $payload = [
            'receiver' => $craftsmanId,
            'subject' => 'Willkommen',
            'body' => 'Hallo auf der Baustelle 2',
            'selfBcc' => false,
            'type' => 4,
        ];

        $this->loginApiConstructionManager($client);
        $this->assertApiPostStatusCodeSame(Response::HTTP_OK, $client, '/api/emails', $payload);

        $expectedPayload = [
            'receiver' => $craftsman->getEmail(),
            'receiverCCs' => $craftsman->getEmailCCs(),
            'receiverBCC' => null,
            'subject' => $payload['subject'],
            'body' => $payload['body'],
            'type' => 'CRAFTSMAN_ISSUE_REMINDER',
        ];
        $this->assertNewIssueEvents($client, $constructionSite->getId(), $craftsman->getId(), [[IssueEventTypes::Email, json_encode($expectedPayload)]]);
    }

    /**
     * @var array array{0: IssueEventTypes, 1: string}[][]
     */
    private array $issueEvents = [];

    /**
     * @param array{0: IssueEventTypes, 1: string}[] $newEntries
     */
    private function assertNewIssueEvents(Client $client, string $constructionSiteId, string $root, array $newEntries): void
    {
        $url = '/api/issue_events?constructionSite='.$constructionSiteId.'&root='.$root.'&order[createdAt]=asc';
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);

        $expectedEntries = $newEntries;
        if (isset($this->issueEvents[$root])) {
            $expectedEntries = [...$this->issueEvents[$root], ...$expectedEntries];
        }
        $this->issueEvents[$root] = $expectedEntries;

        $this->assertSameSize($expectedEntries, $collection['hydra:member']);
        for ($i = 0; $i < count($expectedEntries); ++$i) {
            $expectedEntry = $expectedEntries[$i];
            $actualEntry = $collection['hydra:member'][$i];

            $this->assertEquals($expectedEntry[0]->value, $actualEntry['type']);
            $this->assertEquals($expectedEntry[1], $actualEntry['payload']);
        }
    }
}
