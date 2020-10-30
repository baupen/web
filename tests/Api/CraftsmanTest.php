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
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
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

        $this->assertApiGetResponseStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetResponseStatusCodeSame(Response::HTTP_OK, $client, '/api/craftsmen?constructionSite='.$constructionSite->getId());
        $this->assertContainsOnlyListedFields($response, 'email', 'contactName', 'company', 'trade');
    }

    public function testPostPatchAndDelete()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->findIriBy(ConstructionSite::class, ['id' => $constructionSite->getId()]);
        $affiliation = [
            'constructionSite' => $constructionSiteId,
        ];

        $sample = [
            'contactName' => 'Alex Woodly',
            'company' => 'Wood AG',
            'trade' => 'wood',
            'email' => 'new@craftsman.ch',
        ];

        $this->assertApiPostPayloadMinimal($client, '/api/craftsmen', $sample, $affiliation);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/craftsmen', $sample, $affiliation);
        $this->assertApiCollectionContainsItem($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $response);

        $update = [
            'contactName' => 'Peter Woodly',
            'company' => 'Wood Gmbh',
            'trade' => 'wood & more',
            'email' => 'new@wood.ch',
        ];
        $craftsmanId = json_decode($response->getContent(), true)['@id'];
        $response = $this->assertApiPatchPayloadPersisted($client, $craftsmanId, $update);
        $this->assertApiCollectionContainsItem($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $response);

        $this->assertApiDeleteOk($client, $craftsmanId);
        $this->assertApiCollectionHasNoItemWithId($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $craftsmanId);
    }

    private function assertApiCollectionContainsItem(Client $client, string $url, \ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response $itemResponse)
    {
        $item = json_decode($itemResponse->getContent(), true);
        unset($item['@context']);

        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);
        foreach ($collection['hydra:member'] as $entry) {
            if ($entry == $item) {
                $this->assertTrue($entry == $item);

                return;
            }
        }

        $this->fail('item '.$itemResponse->getContent().' not found in collection '.$collectionResponse->getContent());
    }

    private function assertApiCollectionHasNoItemWithId(Client $client, string $url, string $id)
    {
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);

        foreach ($collection['hydra:member'] as $entry) {
            if ($entry['@id'] == $id) {
                $this->fail('id '.$id.' found in '.$collectionResponse->getContent());

                return;
            }
        }

        $this->assertTrue(true);
    }
}
