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
        $this->assertApiOperationNotAuthorized($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/craftsmen/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');

        $this->loginApiConstructionManagerExternal($client);
        $this->assertApiOperationForbidden($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationForbidden($client, '/api/craftsmen/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/craftsmen?constructionSite='.$constructionSite->getId());
        $this->assertApiResponseFieldSubset($response, 'email', 'contactName', 'company', 'trade', 'isDeleted');
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
        $this->assertApiCollectionContainsResponseItem($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $response);

        $update = [
            'contactName' => 'Peter Woodly',
            'company' => 'Wood Gmbh',
            'trade' => 'wood & more',
            'email' => 'new@wood.ch',
        ];
        $craftsmanId = json_decode($response->getContent(), true)['@id'];
        $response = $this->assertApiPatchPayloadPersisted($client, $craftsmanId, $update);
        $this->assertApiCollectionContainsResponseItem($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $response);

        $this->assertApiDeleteOk($client, $craftsmanId);
        $this->assertApiCollectionContainsResponseItemDeleted($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $response);
    }
}
