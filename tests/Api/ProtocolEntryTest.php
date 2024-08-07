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
        $this->assertApiResponseFieldSubset($response, 'root', 'type', 'payload', 'createdAt', 'createdBy');
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
}
