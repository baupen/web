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
        $this->assertApiOperationForbidden($client, '/api/authentication_tokens/'.$someId, 'GET');
    }

    public function testPost()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $this->markTestIncomplete(
            'Auth token needs to be implemented yet.'
        );

        /*
        $constructionSite = $this->getTestConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];
        $filter = $constructionSite->getFilters()[0];

        $constructionSitePayload = ['constructionSite' => $this->getIriFromItem($constructionSite)];
        $craftsmanPayload = ['craftsman' => $this->getIriFromItem($craftsman)];
        $filterPayload = ['filter' => $this->getIriFromItem($filter)];

        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/authentication_tokens', $constructionSitePayload + $craftsmanPayload);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/authentication_tokens', $constructionSitePayload + $filterPayload);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/authentication_tokens', $craftsmanPayload + $filterPayload);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/authentication_tokens', $constructionSitePayload + $craftsmanPayload + $filterPayload);

        $this->assertApiPostPayloadPersisted($client, '/api/authentication_tokens', [], $constructionSitePayload);
        $this->assertApiPostPayloadPersisted($client, '/api/authentication_tokens', [], $craftsmanPayload);
        $this->assertApiPostPayloadPersisted($client, '/api/authentication_tokens', [], $filterPayload);
         */
    }
}
