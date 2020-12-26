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
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Issue;
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
        $this->assertApiOperationForbidden($client, '/api/craftsmen', 'POST');
        $this->assertApiOperationForbidden($client, '/api/craftsmen/'.$constructionSite->getCraftsmen()[0]->getId(), 'GET', 'PATCH', 'DELETE');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/craftsmen?constructionSite='.$constructionSite->getId());
        $this->assertApiResponseFieldSubset($response, 'email', 'emailCCs', 'contactName', 'company', 'trade', 'isDeleted', 'lastChangedAt');
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

        $sample = [
            'contactName' => 'Alex Woodly',
            'company' => 'Wood AG',
            'trade' => 'wood',
            'email' => 'new@craftsman.ch',
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen', $sample, $affiliation);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/craftsmen', $affiliation, $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/craftsmen', $sample, $affiliation);
        $this->assertApiCollectionContainsResponseItem($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $response);
        $craftsmanId = json_decode($response->getContent(), true)['@id'];

        $emptyConstructionSite = $this->getEmptyConstructionSite();
        $emptyConstructionSiteId = $this->getIriFromItem($emptyConstructionSite);
        $writeProtected = [
            'constructionSite' => $emptyConstructionSiteId,
        ];
        $this->assertApiPatchPayloadIgnored($client, $craftsmanId, $writeProtected);

        $update = [
            'contactName' => 'Peter Woodly',
            'company' => 'Wood Gmbh',
            'trade' => 'wood & more',
            'email' => 'new@wood.ch',
        ];
        $response = $this->assertApiPatchPayloadPersisted($client, $craftsmanId, $update);
        $this->assertApiCollectionContainsResponseItem($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $response);

        $this->assertApiDeleteOk($client, $craftsmanId);
        $this->assertApiCollectionContainsResponseItemDeleted($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $response);
    }

    public function testIsDeletedFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];
        $this->assertFalse($craftsman->getIsDeleted(), 'ensure craftsman is not deleted, else the following tests will fail');

        $craftsmanIri = $this->getIriFromItem($craftsman);
        $this->assertApiCollectionContainsIri($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $craftsmanIri);
        $this->assertApiCollectionContainsIri($client, '/api/craftsmen?constructionSite='.$constructionSite->getId().'&isDeleted=false', $craftsmanIri);
        $this->assertApiCollectionNotContainsIri($client, '/api/craftsmen?constructionSite='.$constructionSite->getId().'&isDeleted=true', $craftsmanIri);
    }

    public function testLastChangedAtFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsmanIri = $this->getIriFromItem($craftsman);

        $this->assertApiCollectionFilterDateTime($client, '/api/craftsmen?constructionSite='.$constructionSite->getId().'&', $craftsmanIri, 'lastChangedAt', $craftsman->getLastChangedAt());
    }

    public function testAllFilters()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);

        $sample = [
            'constructionSite' => $constructionSiteId,
            'contactName' => 'Alex Woodly',
            'company' => 'Wood AG',
            'trade' => 'wood',
            'email' => 'new@craftsman.ch',
        ];

        $response = $this->assertApiPostStatusCodeSame(Response::HTTP_CREATED, $client, '/api/craftsmen', $sample);
        $this->assertApiCollectionContainsResponseItem($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), $response);
        $craftsmanIri = json_decode($response->getContent(), true)['@id'];

        $collectionUrlPrefix = '/api/craftsmen?constructionSite='.$constructionSite->getId().'&';

        $this->assertApiCollectionFilterSearchExact($client, $collectionUrlPrefix, $craftsmanIri, 'id', $craftsmanIri);
        $this->assertApiCollectionFilterSearchExact($client, $collectionUrlPrefix, $craftsmanIri, 'trade', $sample['trade']);
    }

    public function testFeed()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiGetOk($client, '/api/craftsmen/feed_entries?constructionSite='.$constructionSite->getId());
    }

    public function testStatistics()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionManager = $constructionSite->getConstructionManagers()[0];
        $craftsman = $constructionSite->getCraftsmen()[0];

        $yesterday = new \DateTime('yesterday');
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        foreach ([$yesterday, $today, $tomorrow] as $item) {
            $deadlineIssue = $this->createRegisteredIssueForCraftsman($constructionSite, $constructionManager, $craftsman);
            $deadlineIssue->setDeadline($item);
            $this->saveEntity($deadlineIssue);

            $resolvedIssue = $this->createRegisteredIssueForCraftsman($constructionSite, $constructionManager, $craftsman);
            $resolvedIssue->setResolvedAt($item);
            $resolvedIssue->setResolvedBy($craftsman);
            $this->saveEntity($resolvedIssue);

            $closedIssue = $this->createRegisteredIssueForCraftsman($constructionSite, $constructionManager, $craftsman);
            $closedIssue->setClosedAt($item);
            $closedIssue->setClosedBy($constructionManager);
            $this->saveEntity($closedIssue);
        }

        $statistics = $this->getStatisticForCraftsman($client, $craftsman);

        $this->assertEquals(3, $statistics['issueOpenCount']);
        $this->assertEquals(3, $statistics['issueUnreadCount']);
        $this->assertEquals(2, $statistics['issueOverdueCount']);
        $this->assertEquals(3, $statistics['issueClosedCount']);

        $this->assertEquals(null, $statistics['lastEmailReceived']);
        $this->assertEquals(null, $statistics['lastVisitOnline']);

        $this->assertEquals($yesterday->format('c'), $statistics['nextDeadline']);
        $this->assertEquals($tomorrow->format('c'), $statistics['lastIssueResolved']);

        $craftsman = $this->getTestConstructionSite()->getCraftsmen()[0];
        $craftsman->setLastEmailReceived($today);
        $craftsman->setLastVisitOnline($tomorrow);
        $this->saveEntity($craftsman);

        $statistics = $this->getStatisticForCraftsman($client, $craftsman);
        $this->assertEquals($today->format('c'), $statistics['lastEmailReceived']);
        $this->assertEquals($tomorrow->format('c'), $statistics['lastVisitOnline']);
        $this->assertEquals(0, $statistics['issueUnreadCount']);
    }

    private function createRegisteredIssueForCraftsman(ConstructionSite $constructionSite, ConstructionManager $constructionManager, Craftsman $craftsman): Issue
    {
        $issue = new Issue();

        $issue->setConstructionSite($constructionSite);
        $issue->setNumber(0);

        $issue->setCreatedAt(new \DateTime());
        $issue->setCreatedBy($constructionManager);

        $issue->setRegisteredAt(new \DateTime());
        $issue->setRegisteredBy($constructionManager);

        $issue->setCraftsman($craftsman);

        return $issue;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param $craftsmanIri
     */
    private function getStatisticForCraftsman(Client $client, Craftsman $craftsman): array
    {
        $craftsmanIri = $this->getIriFromItem($craftsman);

        $response = $this->assertApiGetOk($client, '/api/craftsmen/statistics?constructionSite='.$craftsman->getConstructionSite()->getId());
        $craftsmenStatistics = json_decode($response->getContent(), true);
        $statistics = [];
        foreach ($craftsmenStatistics as $craftsmenStatistic) {
            if ($craftsmenStatistic['craftsman'] === $craftsmanIri) {
                $statistics = $craftsmenStatistic;
            }
        }

        return $statistics;
    }
}
