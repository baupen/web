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

    public function skipTestPostPatchAndDelete()
    {
        // TODO: continue
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $map = $constructionSite->getMaps()[0];
        $mapId = $this->getIriFromItem($map);
        $affiliation = [
            'constructionSite' => $constructionSiteId,
            'map' => $mapId,
        ];

        $sample = [
            'wasAddedByClient' => true,
            'isMarked' => true,
        ];

        $optionalProperties = [
            'description' => 'hello world',
        ];

        $this->assertApiPostPayloadMinimal($client, '/api/issues', $sample, $affiliation);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/issues', array_merge($sample, $optionalProperties), $affiliation);
        $this->assertApiCollectionContainsResponseItem($client, '/api/issues?constructionSite='.$constructionSite->getId(), $response);

        $update = [
            'wasAddedByClient' => false,
            'isMarked' => false,
        ];
        $issueId = json_decode($response->getContent(), true)['@id'];
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

    public function testLastChangedAtFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $issue = $constructionSite->getIssues()[0];
        $issueIri = $this->getIriFromItem($issue);

        $tomorrow = new \DateTime('tomorrow');
        $dateTimeString = DateTimeFormatter::toStringUTCTimezone($tomorrow); // like 2020-10-30T23:00:00.000000Z
        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&lastChangedAt[after]='.$dateTimeString, $issueIri);

        $lastChangedAt = $issue->getLastChangedAt();
        $dateTimeString = DateTimeFormatter::toStringUTCTimezone($lastChangedAt); // like 2020-10-30T23:00:00.000000Z
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSite->getId().'&lastChangedAt[after]='.$dateTimeString, $issueIri);
    }
}
