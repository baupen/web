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

class FeedEntryTest extends ApiTestCase
{
    use FixturesTrait;
    use TestDataTrait;
    use AssertApiTrait;
    use AuthenticationTrait;

    public function testInvalidMethods()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $testUser = $this->loginApiConstructionManager($client);

        $this->assertApiOperationUnsupported($client, '/api/feed_entries', 'POST');
        $this->assertApiOperationUnsupported($client, '/api/feed_entries/'.$testUser->getId(), 'DELETE', 'PUT', 'PATCH');
    }

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $this->assertApiOperationNotAuthorized($client, '/api/feed_entries', 'GET');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);
        $constructionSite = $this->getTestConstructionSite();

        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/feed_entries');

        $response = $this->assertApiGetOk($client, '/api/feed_entries?constructionSite='.$constructionSite->getId());
        $this->assertApiResponseFieldSubset($response, 'craftsman');
    }
}
