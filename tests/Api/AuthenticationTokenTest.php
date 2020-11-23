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
use Symfony\Component\HttpFoundation\Response;

class AuthenticationTokenTest extends ApiTestCase
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
        $this->assertApiOperationNotAuthorized($client, '/api/authentication_tokens', 'POST');
        $someId = $constructionSite->getId();
        $this->assertApiOperationNotAuthorized($client, '/api/authentication_tokens/'.$someId, 'GET');

        $this->loginApiConstructionManagerExternal($client);
        $this->assertApiOperationForbidden($client, '/api/authentication_tokens', 'POST');
    }

    public function testPost()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);

        $constructionSite = $constructionManager->getConstructionSites()[0];
        $craftsman = $constructionSite->getCraftsmen()[0];
        $filter = $constructionSite->getFilters()[0];

        $constructionManagerPayload = ['constructionManager' => $this->getIriFromItem($constructionManager)];
        $craftsmanPayload = ['craftsman' => $this->getIriFromItem($craftsman)];
        $filterPayload = ['filter' => $this->getIriFromItem($filter)];

        $this->assertApiPostPayloadPersisted($client, '/api/authentication_tokens', [], $constructionManagerPayload);
        $this->assertApiPostPayloadPersisted($client, '/api/authentication_tokens', [], $craftsmanPayload);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/authentication_tokens', [], $filterPayload);
        $this->assertApiResponseFieldSubset($response, 'token');

        $this->assertApiPostStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/authentication_tokens', $constructionManagerPayload + $craftsmanPayload);
        $this->assertApiPostStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/authentication_tokens', $constructionManagerPayload + $filterPayload);
        $this->assertApiPostStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/authentication_tokens', $craftsmanPayload + $filterPayload);
        $this->assertApiPostStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/authentication_tokens', $constructionManagerPayload + $craftsmanPayload + $filterPayload);
        $this->assertApiPostStatusCodeSame(Response::HTTP_FORBIDDEN, $client, '/api/authentication_tokens', []);
    }

    public function testTokenAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);
        $constructionSite = $this->getTestConstructionSite();
        $otherConstructionSite = $this->getEmptyConstructionSite();

        $map = $constructionSite->getMaps()[0];
        $otherMap = $this->addMap($otherConstructionSite);

        $craftsman = $constructionSite->getCraftsmen()[0];
        $otherCraftsman = $this->addCraftsman($otherConstructionSite);

        $tokens = [
            $this->createApiTokenFor($constructionSite->getConstructionManagers()[0]),
            $this->createApiTokenFor($constructionSite->getCraftsmen()[0]),
            $this->createApiTokenFor($constructionSite->getFilters()[0]),
        ];

        foreach ($tokens as $token) {
            $this->assertApiTokenRequestNotSuccessful($client, $token, 'GET', '/api/construction_sites/someid');
            $this->assertApiTokenRequestSuccessful($client, $token, 'GET', '/api/construction_sites/'.$constructionSite->getId());
            if ($token !== $tokens[0]) { // construction managers can access all / post construction sites
                $this->assertApiTokenRequestNotSuccessful($client, $token, 'GET', '/api/construction_sites/'.$otherConstructionSite->getId());
                $this->assertApiTokenRequestNotSuccessful($client, $token, 'POST', '/api/construction_sites');
            }
        }

        foreach ($tokens as $token) {
            $this->assertApiTokenRequestNotSuccessful($client, $token, 'GET', '/api/craftsmen/someid');
            $this->assertApiTokenRequestSuccessful($client, $token, 'GET', '/api/craftsmen/'.$craftsman->getId());
            $this->assertApiTokenRequestNotSuccessful($client, $token, 'GET', '/api/craftsmen/'.$otherCraftsman->getId());
            if ($token !== $tokens[0]) { // construction managers can create craftsman
                $this->assertApiTokenRequestNotSuccessful($client, $token, 'POST', '/api/craftsmen');
            }
        }

        foreach ($tokens as $token) {
            $this->assertApiTokenRequestNotSuccessful($client, $token, 'GET', '/api/maps/someid');
            $this->assertApiTokenRequestSuccessful($client, $token, 'GET', '/api/maps/'.$map->getId());
            $this->assertApiTokenRequestNotSuccessful($client, $token, 'GET', '/api/maps/'.$otherMap->getId());
            if ($token !== $tokens[0]) { // construction managers can create craftsman
                $this->assertApiTokenRequestNotSuccessful($client, $token, 'POST', '/api/maps');
            }
        }
    }
}
