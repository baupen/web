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
use App\Enum\ProtocolEntryTypes;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\DataFixtures\TestProtocolEntryFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Symfony\Component\HttpFoundation\Response;

class ProtocolEntryTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestProtocolEntryFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/protocol_entries?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/protocol_entries/'.$constructionSite->getId(), 'GET', 'DELETE');
        $this->assertApiOperationUnsupported($client, '/api/protocol_entries/'.$constructionSite->getId(), 'PATCH');

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiOperationForbidden($client, '/api/protocol_entries', 'POST');
        $this->assertApiOperationForbidden($client, '/api/protocol_entries/'.$constructionSite->getProtocolEntries()[0]->getId(), 'GET', 'DELETE');
        $this->assertApiOperationUnsupported($client, '/api/protocol_entries/'.$constructionSite->getId(), 'PATCH');
    }

    public function testGet(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestProtocolEntryFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/protocol_entries');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/protocol_entries?constructionSite='.$constructionSite->getId());
        $this->assertApiResponseFieldSubset($response, 'root', 'type', 'payload', 'createdAt', 'createdBy', 'isDeleted');
    }

    public function testPostAndDelete(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestProtocolEntryFixtures::class]);
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
            'type' => ProtocolEntryTypes::Text->value,
            'payload' => 'Hello World',
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/protocol_entries', $sample, $affiliation);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/protocol_entries', $affiliation, $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/protocol_entries', array_merge($sample, $optionalProperties), $affiliation);
        $this->assertApiCollectionContainsResponseItem($client, '/api/protocol_entries?constructionSite='.$constructionSite->getId(), $response);
        $protocolEntryId = json_decode($response->getContent(), true)['@id'];

        $this->assertApiDeleteOk($client, $protocolEntryId);
        $this->assertApiCollectionContainsResponseItemDeleted($client, '/api/protocol_entries?constructionSite='.$constructionSite->getId(), $response);
    }

    public function testIsDeletedFilter(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestProtocolEntryFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $protocolEntry = $constructionSite->getProtocolEntries()[0];
        $this->assertFalse($protocolEntry->getIsDeleted(), 'ensure not deleted, else the following tests will fail');

        $protocolEntryIri = $this->getIriFromItem($protocolEntry);
        $this->assertApiCollectionContainsIri($client, '/api/protocol_entries?constructionSite='.$constructionSite->getId(), $protocolEntryIri);
        $this->assertApiCollectionContainsIri($client, '/api/protocol_entries?constructionSite='.$constructionSite->getId().'&isDeleted=false', $protocolEntryIri);
        $this->assertApiCollectionNotContainsIri($client, '/api/protocol_entries?constructionSite='.$constructionSite->getId().'&isDeleted=true', $protocolEntryIri);
    }

    public function testCreatedAtFilter(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestProtocolEntryFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $protocolEntry = $constructionSite->getProtocolEntries()[0];
        $protocolEntryIri = $this->getIriFromItem($protocolEntry);

        $this->assertApiCollectionFilterDateTime($client, '/api/protocol_entries?constructionSite='.$constructionSite->getId().'&', $protocolEntryIri, 'createdAt', $protocolEntry->getCreatedAt());
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
        $payload = ['registeredBy' => $constructionManagerId, 'registeredAt' => $time];
        $response = $this->assertApiPostPayloadPersisted($client, '/api/issues', $payload, $basePayload);
        $currentIssue = json_decode($response->getContent(), true);
        $issueId = substr($currentIssue['@id'], strrpos($currentIssue['@id'], '/') + 1);
        $this->assertProtocolEntries($client, $constructionSite->getId(), $issueId, []);

        // change resolved by, hence expect corresponding log entry
        $payload = ['resolvedBy' => $craftsmanId, 'resolvedAt' => $time];
        $response = $this->assertApiPatchOk($client, '/api/issues/'.$issueId, array_merge($currentIssue, $payload));
        $currentIssue = json_decode($response->getContent(), true);
        $this->assertProtocolEntries($client, $constructionSite->getId(), $issueId, [[ProtocolEntryTypes::StatusSet, 'RESOLVED']]);

        sleep(1); // to enforce order

        $payload = ['closedBy' => $constructionManagerId, 'closedAt' => $time];
        $response = $this->assertApiPatchOk($client, '/api/issues/'.$issueId, array_merge($currentIssue, $payload));
        $currentIssue = json_decode($response->getContent(), true);
        $this->assertProtocolEntries($client, $constructionSite->getId(), $issueId, [[ProtocolEntryTypes::StatusSet, 'RESOLVED'], [ProtocolEntryTypes::StatusSet, 'CLOSED']]);

        sleep(1); // to enforce order

        $time = (new \DateTime())->format('c');
        $payload = ['closedBy' => $constructionManagerId, 'closedAt' => $time];
        $response = $this->assertApiPatchOk($client, '/api/issues/'.$issueId, array_merge($currentIssue, $payload));
        $currentIssue = json_decode($response->getContent(), true);
        $this->assertProtocolEntries($client, $constructionSite->getId(), $issueId, [[ProtocolEntryTypes::StatusSet, 'RESOLVED'], [ProtocolEntryTypes::StatusSet, 'CLOSED'], [ProtocolEntryTypes::StatusSet, 'CLOSED']]);

        sleep(1); // to enforce order

        $payload = ['resolvedBy' => null, 'resolvedAt' => null, 'closedBy' => null, 'closedAt' => null];
        $response = $this->assertApiPatchOk($client, '/api/issues/'.$issueId, array_merge($currentIssue, $payload));
        json_decode($response->getContent(), true);
        $this->assertProtocolEntries($client, $constructionSite->getId(), $issueId, [[ProtocolEntryTypes::StatusSet, 'RESOLVED'], [ProtocolEntryTypes::StatusSet, 'CLOSED'], [ProtocolEntryTypes::StatusSet, 'CLOSED'], [ProtocolEntryTypes::StatusUnset, 'RESOLVED'], [ProtocolEntryTypes::StatusUnset, 'CLOSED']]);
    }

    /**
     * @param array{0: ProtocolEntryTypes, 1: string}[] $expectedEntries
     */
    private function assertProtocolEntries(Client $client, string $constructionSiteId, string $root, array $expectedEntries): void
    {
        $url = '/api/protocol_entries?constructionSite='.$constructionSiteId.'&root='.$root.'&order[createdAt]=asc';
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);

        $this->assertSameSize($expectedEntries, $collection['hydra:member']);
        for ($i = 0; $i < count($expectedEntries); ++$i) {
            $expectedEntry = $expectedEntries[$i];
            $actualEntry = $collection['hydra:member'][$i];

            $this->assertEquals($expectedEntry[0]->value, $actualEntry['type']);
            $this->assertEquals($expectedEntry[1], $actualEntry['payload']);
        }
    }
}
