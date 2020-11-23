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
use App\Tests\DataFixtures\TestFilterFixtures;
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
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/filters', 'POST');
        $someId = $constructionSite->getId();
        $this->assertApiOperationNotAuthorized($client, '/api/filters/'.$someId, 'GET');

        $this->loginApiConstructionManagerExternal($client);
        $this->assertApiOperationForbidden($client, '/api/filters', 'POST');
        $this->assertApiOperationForbidden($client, '/api/filters/'.$constructionSite->getFilters()[0]->getId(), 'GET');
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

    public function testGetIssues()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->markTestIncomplete(
            'Auth token not implemented yet.'
        );
        /*
        $constructionSite = $this->getTestConstructionSite();
        $issue = $constructionSite->getIssues()[0];
        $issueIri = $this->getIriFromItem($issue);
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = ['constructionSite' => $constructionSiteId];

        $sample = ['isMarked' => $issue->getIsMarked()];
        $filterId = $this->postFilter($client, $sample, $affiliation);
        $client->setDefaultOptions(['headers' => ['x-authenticate-filter' => $filterId]]);
        $this->assertApiCollectionContainsIri($client, '/api/issues?constructionSite='.$constructionSiteId, $issueIri);

        $sample = ['isMarked' => !$issue->getIsMarked()];
        $filterId = $this->postFilter($client, $sample, $affiliation);
        $client->setDefaultOptions(['headers' => ['x-authenticate-filter' => $filterId]]);
        $this->assertApiCollectionNotContainsIri($client, '/api/issues?constructionSite='.$constructionSiteId, $issueIri);
        */
    }

    public function testAccessAllowedUntilEnforced()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->markTestIncomplete(
            'Auth token not implemented yet.'
        );
        /*
        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = ['constructionSite' => $constructionSiteId];

        $sample = ['accessAllowedUntil' => (new \DateTime('tomorrow'))->format('c')];
        $filterId = $this->postFilter($client, $sample, $affiliation);
        $client->setDefaultOptions([
            'headers' => ['x-authenticate-filter' => $filterId],
        ]);
        $this->assertApiGetOk($client, '/api/issues?constructionSite='.$constructionSiteId);

        $sample = ['accessAllowedUntil' => (new \DateTime('yesterday'))->format('c')];
        $filterId = $this->postFilter($client, $sample, $affiliation);
        $this->assertApiOperationForbidden($client, '/api/issues?filter='.$filterId, 'GET');
        */
    }

    private function postFilter(Client $client, array $payload, array $additionalPayload): string
    {
        $response = $this->assertApiPostPayloadPersisted($client, '/api/filters', $payload, $additionalPayload);

        $iri = json_decode($response->getContent(), true)['@id'];

        return substr($iri, strrpos($iri, '/') + 1);
    }
}
