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
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;

class FilterTest extends ApiTestCase
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
        $this->assertApiOperationNotAuthorized($client, '/api/filters', 'POST');
        $someId = $constructionSite->getId();
        // $this->assertApiOperationNotAuthorized($client, '/api/filters/'.$someId.'/issues', 'GET');
        // $this->assertApiOperationNotAuthorized($client, '/api/filters/'.$someId.'/report', 'GET');

        $this->loginApiConstructionManagerExternal($client);
        $this->assertApiOperationForbidden($client, '/api/filters', 'POST');
        // $this->assertApiOperationForbidden($client, '/api/filters/'.$someId.'/issues', 'GET');
        // $this->assertApiOperationForbidden($client, '/api/filters/'.$someId.'/report', 'GET');
    }

    public function testPost()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = [
            'constructionSite' => $constructionSiteId,
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/filters', $affiliation);
        $this->assertApiPostPayloadPersisted($client, '/api/filters', [], $affiliation);
    }

    public function ignoreTestAccessAllowedUntilEnforced()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = ['constructionSite' => $constructionSiteId];

        $sample = ['accessAllowedUntil' => (new \DateTime('tomorrow'))->format('c')];
        $filterId = $this->postFilter($client, $sample, $affiliation);
        $this->assertApiGetOk($client, '/api/filters/'.$filterId.'/issues');

        $sample = ['accessAllowedUntil' => (new \DateTime('yesterday'))->format('c')];
        $filterId = $this->postFilter($client, $sample, $affiliation);
        $this->assertApiOperationForbidden($client, '/api/filters/'.$filterId.'/issues', 'GET');
    }

    public function ignoreTestGetIssues()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $issue = $constructionSite->getIssues()[0];
        $issueIri = $this->getIriFromItem($issue);
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = ['constructionSite' => $constructionSiteId];

        $sample = ['isMarked' => $issue->getIsMarked()];
        $filterId = $this->postFilter($client, $sample, $affiliation);
        $this->assertApiCollectionContainsIri($client, '/api/filters/'.$filterId.'/issues', $issueIri);

        $sample = ['isMarked' => !$issue->getIsMarked()];
        $filterId = $this->postFilter($client, $sample, $affiliation);
        $this->assertApiCollectionNotContainsIri($client, '/api/filters/'.$filterId.'/issues', $issueIri);
    }

    private function postFilter(Client $client, array $payload, array $additionalPayload): string
    {
        $response = $this->assertApiPostPayloadPersisted($client, '/api/filters', $payload, $additionalPayload);

        return json_decode($response->getContent(), true)['@id'];
    }
}
