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

        $constructionManager = $this->getTestConstructionManager();
        $constructionManagerIri = $this->getIriFromItem($constructionManager);
        $constructionManagerToken = $this->createApiTokenFor($constructionManager);

        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsmanIri = $this->getIriFromItem($craftsman);
        $craftsmanToken = $this->createApiTokenFor($craftsman);

        $filter = $constructionSite->getFilters()[0];
        $filterIri = $this->getIriFromItem($filter);
        $filterToken = $this->createApiTokenFor($filter);

        $jsonUrlEscape = function (string $value) {
            return str_replace('/', '\\/', $value);
        };

        $response = $this->assertApiTokenRequestSuccessful($client, $constructionManagerToken, 'GET', '/api/me');
        $this->assertStringContainsString($jsonUrlEscape($constructionManagerIri), $response->getContent());

        $response = $this->assertApiTokenRequestSuccessful($client, $craftsmanToken, 'GET', '/api/me');
        $this->assertStringContainsString($jsonUrlEscape($craftsmanIri), $response->getContent());

        $response = $this->assertApiTokenRequestSuccessful($client, $filterToken, 'GET', '/api/me');
        $this->assertStringContainsString($jsonUrlEscape($filterIri), $response->getContent());
    }
}
