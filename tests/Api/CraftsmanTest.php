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
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Helper\DateTimeFormatter;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CraftsmanTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/craftsmen?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/craftsmen/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiOperationForbidden($client, '/api/craftsmen', 'POST');
        $this->assertApiOperationForbidden($client, '/api/craftsmen/'.$constructionSite->getCraftsmen()[0]->getId(), 'GET', 'PATCH', 'DELETE');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen');

        $constructionSite = $this->getTestConstructionSite();
        $response = $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/craftsmen?constructionSite='.$constructionSite->getId());
        $this->assertApiResponseFieldSubset($response, 'email', 'emailCCs', 'contactName', 'company', 'trade', 'resolveUrl', 'isDeleted', 'lastChangedAt', 'canEdit');
    }

    public function testCanEdit()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $constructionManager = $constructionSite->getConstructionManagers()[0];
        $map = $constructionSite->getMaps()[0];

        $craftsman = $this->addCraftsman($constructionSite);
        $craftsman->setCanEdit(false);

        $issue = $this->addIssue($constructionSite, $constructionManager);
        $issue->setCraftsman($craftsman);
        $issue->setMap($map);
        $issue->setRegisteredAt(new \DateTime());
        $issue->setRegisteredBy($constructionManager);

        $this->saveEntity($issue, $craftsman);

        $issueId = $this->getIriFromItem($issue);
        $craftsmanId = $this->getIriFromItem($craftsman);
        $craftsmanToken = $this->createApiTokenFor($craftsman);

        $patch = ['resolvedAt' => (new \DateTime())->format('c'), 'resolvedBy' => $craftsmanId];
        $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'PATCH', $issueId, $patch);

        $this->reloadEntity($craftsman);
        $craftsman->setCanEdit(true);
        $this->saveEntity($craftsman);

        $this->assertApiTokenRequestSuccessful($client, $craftsmanToken, 'PATCH', $issueId, $patch);
    }

    public function testPostPatchAndDelete()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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

        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/craftsmen', $sample, $affiliation);
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
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsmanIri = $this->getIriFromItem($craftsman);

        $this->assertApiCollectionFilterDateTime($client, '/api/craftsmen?constructionSite='.$constructionSite->getId().'&', $craftsmanIri, 'lastChangedAt', $craftsman->getLastChangedAt());
    }

    public function testAllFilters()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
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

    public function testFeedEntries()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getEmptyConstructionSite();
        $constructionManager = $this->getTestConstructionManager();
        $this->assignConstructionManager($constructionSite, $constructionManager);
        $craftsman1 = $this->addCraftsman($constructionSite);
        $craftsman1->setLastVisitOnline(new \DateTime('today'));
        $craftsman2 = $this->addCraftsman($constructionSite);
        $craftsman2->setLastVisitOnline(new \DateTime('yesterday + 2 hours'));
        $this->saveEntity($craftsman1, $craftsman2);

        $response = $this->assertApiGetOk($client, '/api/craftsmen/feed_entries?constructionSite='.$constructionSite->getId());
        $feedEntries = json_decode($response->getContent(), true);

        $craftsman1Iri = $this->getIriFromItem($craftsman1);
        $craftsman2Iri = $this->getIriFromItem($craftsman2);

        $expectedCombinations = [
            [
                (new \DateTime('today'))->format(DateTimeFormatter::ISO_DATE_FORMAT),
                $craftsman1Iri,
                10,
                1,
            ],
            [
                (new \DateTime('yesterday'))->format(DateTimeFormatter::ISO_DATE_FORMAT),
                $craftsman2Iri,
                10,
                1,
            ],
        ];

        foreach ($feedEntries['hydra:member'] as $feedEntry) {
            $foundCombinationIndex = null;
            for ($i = 0; $i < count($expectedCombinations); ++$i) {
                $expectedCombination = $expectedCombinations[$i];
                if ($expectedCombination[0] === $feedEntry['date'] &&
                    $expectedCombination[1] === $feedEntry['subject'] &&
                    $expectedCombination[2] === $feedEntry['type'] &&
                    $expectedCombination[3] === $feedEntry['count']) {
                    $foundCombinationIndex = $i;
                    break;
                }
            }

            $this->assertNotNull($foundCombinationIndex, 'not any of the expected combinations');
            unset($expectedCombinations[$foundCombinationIndex]);
            $expectedCombinations = array_values($expectedCombinations);
        }
    }

    public function testStatistics()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionManager = $this->getTestConstructionManager();
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

        $this->assertEquals(3, $statistics['issueSummary']['openCount']);
        $this->assertEquals(3, $statistics['issueSummary']['inspectableCount']);
        $this->assertEquals(3, $statistics['issueSummary']['closedCount']);

        $this->assertEquals(3, $statistics['issueUnreadCount']);
        $this->assertEquals(2, $statistics['issueOverdueCount']);

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
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getStatisticForCraftsman(Client $client, Craftsman $craftsman): array
    {
        $craftsmanIri = $this->getIriFromItem($craftsman);

        $response = $this->assertApiGetOk($client, '/api/craftsmen/statistics?constructionSite='.$craftsman->getConstructionSite()->getId());
        $craftsmenStatistics = json_decode($response->getContent(), true);
        $statistics = [];
        foreach ($craftsmenStatistics['hydra:member'] as $craftsmenStatistic) {
            if ($craftsmenStatistic['craftsman'] === $craftsmanIri) {
                $statistics = $craftsmenStatistic;
            }
        }

        return $statistics;
    }
}
