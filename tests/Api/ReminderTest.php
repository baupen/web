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
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\DataFixtures\TestReminderFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Symfony\Component\HttpFoundation\Response;

class ReminderTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestReminderFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/reminders?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/reminders/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiOperationForbidden($client, '/api/reminders', 'POST');
        $this->assertApiOperationForbidden($client, '/api/reminders/'.$constructionSite->getReminders()[0]->getId(), 'GET', 'PATCH', 'DELETE');
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

        $sample = [
            'description' => 'Remind me about stuff',
            'createdBy' => $constructionManagerId,
            'createdAt' => (new \DateTime())->format('c'),
        ];

        $optionalProperties = [
            'deadline' => (new \DateTime())->format('c'),
            'closedAt' => (new \DateTime())->format('c'),
            'closedBy' => $constructionManagerId,
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/reminders', $sample, $affiliation);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/reminders', $affiliation, $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/reminders', array_merge($sample, $optionalProperties), $affiliation);

        // test GET returns correct fields
        $this->assertApiCollectionContainsResponseItem($client, '/api/reminders?constructionSite='.$constructionSite->getId(), $response);
        $this->assertApiResponseFieldSubset($response, 'description', 'createdBy', 'createdAt', 'deadline', 'closedBy', 'closedAt');

        $reminderId = json_decode($response->getContent(), true)['@id'];

        // test construction site can not be changed anymore
        $emptyConstructionSite = $this->getEmptyConstructionSite();
        $emptyConstructionSiteId = $this->getIriFromItem($emptyConstructionSite);
        $otherConstructionManager = $this->getTestAssociatedConstructionManager();
        $otherConstructionManagerId = $this->getIriFromItem($otherConstructionManager);
        $writeProtected = [
            'constructionSite' => $emptyConstructionSiteId,
            'createdBy' => $otherConstructionManagerId,
            'createdAt' => (new \DateTime('now'))->format('c'),
        ];
        $this->assertApiPatchPayloadIgnored($client, $reminderId, $writeProtected);

        // test PATCH applies changes
        $update = [
            'description' => 'Remind me about other stuff',
            'deadline' => (new \DateTime('now'))->format('c'),
            'closedBy' => $otherConstructionManagerId,
            'closedAt' => (new \DateTime('now'))->format('c'),
        ];
        $response = $this->assertApiPatchPayloadPersisted($client, $reminderId, $update);
        $this->assertApiCollectionContainsResponseItem($client, '/api/reminders?constructionSite='.$constructionSite->getId(), $response);

        // test DELETE removes item
        $this->assertApiDeleteOk($client, $reminderId);
        $this->assertApiCollectionNotContainsIri($client, '/api/reminders?constructionSite='.$constructionSite->getId(), $reminderId);
    }
}
