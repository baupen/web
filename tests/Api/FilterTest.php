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
use App\Tests\DataFixtures\TestFilterFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use App\Tests\Traits\FixturesTrait;
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
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/filters', 'POST');
        $someId = $constructionSite->getId();
        $this->assertApiOperationNotAuthorized($client, '/api/filters/'.$someId, 'GET');

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiOperationForbidden($client, '/api/filters', 'POST');
        $this->assertApiOperationForbidden($client, '/api/filters/'.$constructionSite->getFilters()[0]->getId(), 'GET');
    }

    public function testPost()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = [
            'constructionSite' => $constructionSiteId,
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/filters', $affiliation);
        $this->assertApiPostPayloadPersisted($client, '/api/filters', [], $affiliation);
    }
}
