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
use App\Helper\DateTimeFormatter;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MapTest extends ApiTestCase
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
        $this->assertApiOperationNotAuthorized($client, '/api/maps?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/maps/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');

        $this->loginApiConstructionManagerExternal($client);
        $this->assertApiOperationForbidden($client, '/api/maps?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationForbidden($client, '/api/maps/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/maps');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/maps?constructionSite='.$constructionSite->getId());
        $this->assertApiResponseFieldSubset($response, 'name', 'parent', 'file', 'fileUrl', 'isDeleted', 'lastChangedAt');
        $this->assertApiResponseFileIsDownloadable($client, $response, 'fileUrl', ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    public function testPostPatchAndDelete()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = [
            'constructionSite' => $constructionSiteId,
        ];

        $parent = $constructionSite->getMaps()[0];
        $parentId = $this->getIriFromItem($parent);

        $file = $constructionSite->getMapFiles()[0];
        $fileId = $this->getIriFromItem($file);

        $sample = [
            'name' => 'OG 2',
        ];

        $optionalProperties = [
            'parent' => $parentId,
            'file' => $fileId,
        ];

        $this->assertApiPostPayloadMinimal($client, '/api/maps', $sample, $affiliation);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/maps', array_merge($sample, $optionalProperties), $affiliation);
        $this->assertApiCollectionContainsResponseItem($client, '/api/maps?constructionSite='.$constructionSite->getId(), $response);

        $update = [
            'name' => 'OG 3',
            'parent' => null,
            'file' => null,
        ];
        $mapId = json_decode($response->getContent(), true)['@id'];
        $response = $this->assertApiPatchPayloadPersisted($client, $mapId, $update);
        $this->assertApiCollectionContainsResponseItem($client, '/api/maps?constructionSite='.$constructionSite->getId(), $response);

        $this->assertApiDeleteOk($client, $mapId);
        $this->assertApiCollectionContainsResponseItemDeleted($client, '/api/maps?constructionSite='.$constructionSite->getId(), $response);
    }

    public function testIsDeletedFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $map = $constructionSite->getMaps()[0];
        $this->assertFalse($map->getIsDeleted(), 'ensure map is not deleted, else the following tests will fail');

        $mapIri = $this->getIriFromItem($map);
        $this->assertApiCollectionContainsIri($client, '/api/maps?constructionSite='.$constructionSite->getId(), $mapIri);
        $this->assertApiCollectionContainsIri($client, '/api/maps?constructionSite='.$constructionSite->getId().'&isDeleted=false', $mapIri);
        $this->assertApiCollectionNotContainsIri($client, '/api/maps?constructionSite='.$constructionSite->getId().'&isDeleted=true', $mapIri);
    }

    public function testLastChangedAtFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $map = $constructionSite->getMaps()[0];
        $mapIri = $this->getIriFromItem($map);

        $tomorrow = new \DateTime('tomorrow');
        $dateTimeString = DateTimeFormatter::toStringUTCTimezone($tomorrow); // like 2020-10-30T23:00:00.000000Z
        $this->assertApiCollectionNotContainsIri($client, '/api/maps?constructionSite='.$constructionSite->getId().'&lastChangedAt[after]='.$dateTimeString, $mapIri);

        $lastChangedAt = $map->getLastChangedAt();
        $dateTimeString = DateTimeFormatter::toStringUTCTimezone($lastChangedAt); // like 2020-10-30T23:00:00.000000Z
        $this->assertApiCollectionContainsIri($client, '/api/maps?constructionSite='.$constructionSite->getId().'&lastChangedAt[after]='.$dateTimeString, $mapIri);
    }
}
