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
use App\Tests\DataFixtures\TestUserFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;

class ConstructionManagerTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestUserFixtures::class]);
        $this->loginTestUser($client->getKernelBrowser());

        $response = $client->request('GET', '/api/construction_managers', [
            'headers' => ['Content-Type' => 'application/json'],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertContainsOnlyListedFields($response, 'givenName', 'familyName', 'email', 'phone');
    }
}
