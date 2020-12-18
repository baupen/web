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
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\DataFixtures\TestFilterFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;

class ApiControllerTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $this->assertApiOperationNotAuthorized($client, '/api/me', 'GET');
    }

    public function testMe()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();

        $constructionManager = $constructionSite->getConstructionManagers()[0];
        $constructionManagerToken = $this->createApiTokenFor($constructionManager);

        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsmanToken = $this->createApiTokenFor($craftsman);

        $filter = $constructionSite->getFilters()[0];
        $filterToken = $this->createApiTokenFor($filter);

        $response = $this->assertApiTokenRequestSuccessful($client, $constructionManagerToken, 'GET', '/api/me');
        $this->assertStringContainsString($constructionManager->getId(), $response->getContent());

        $response = $this->assertApiTokenRequestSuccessful($client, $craftsmanToken, 'GET', '/api/me');
        $this->assertStringContainsString($craftsman->getId(), $response->getContent());

        $response = $this->assertApiTokenRequestSuccessful($client, $filterToken, 'GET', '/api/me');
        $this->assertStringContainsString($filter->getId(), $response->getContent());
    }
}
