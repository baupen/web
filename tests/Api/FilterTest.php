<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Filter;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\DataFixtures\TestFilterFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Symfony\Component\HttpFoundation\Response;

class FilterTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/filters', 'POST');
        $someId = $constructionSite->getId();
        $this->assertApiOperationNotAuthorized($client, '/api/filters/' . $someId, 'GET');

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiOperationForbidden($client, '/api/filters', 'POST');
        $this->assertApiOperationForbidden($client, '/api/filters/' . $constructionSite->getFilters()[0]->getId(), 'GET');
    }

    public function testPost(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = [
            'constructionSite' => $constructionSiteId,
        ];

        $this->assertApiPostPayloadPersisted($client, '/api/filters', [], $affiliation);
        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/filters', $affiliation);
    }

    public function testAuthentication(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionSite = $this->getTestConstructionSite();

        $filter = new Filter();
        $filter->setConstructionSite($constructionSite);
        $filter->setAuthenticationToken();
        $this->saveEntity($filter);

        $this->loginConstructionManager($client->getKernelBrowser());
        $this->assertApiGetStatusCodeSame(Response::HTTP_UNAUTHORIZED, $client, '/api/construction_managers');

        $client->setDefaultOptions(['headers' => ['X-AUTHENTICATION' => ['invalid']]]);
        $this->assertApiGetStatusCodeSame(Response::HTTP_UNAUTHORIZED, $client, '/api/construction_managers');

        $client->setDefaultOptions(['headers' => ['X-AUTHENTICATION' => $filter->getAuthenticationToken()]]);
        $this->assertApiGetOk($client, '/api/construction_managers');
    }
}
