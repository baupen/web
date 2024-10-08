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
use App\Tests\DataFixtures\TestEmailTemplateFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AssertEmailTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Symfony\Component\HttpFoundation\Response;

class EmailTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use AssertEmailTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestEmailTemplateFixtures::class]);

        $this->assertApiOperationUnsupported($client, '/api/emails', 'GET');
        $this->assertApiOperationNotAuthorized($client, '/api/emails/someid', 'GET');
        $this->assertApiOperationUnsupported($client, '/api/emails/someid', 'PATCH');
        $this->assertApiOperationUnsupported($client, '/api/emails/someid', 'DELETE');
        $this->assertApiOperationNotAuthorized($client, '/api/emails', 'POST');
    }

    public function testPost(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsmanId = $this->getIriFromItem($craftsman);

        $payload = [
            'receiver' => $craftsmanId,
            'subject' => 'Willkommen',
            'body' => 'Hallo auf der Baustelle 2',
            'selfBcc' => false,
            'type' => 4,
        ];

        $this->loginApiConstructionManager($client);
        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/emails', $payload);
        $this->assertApiPostStatusCodeSame(Response::HTTP_OK, $client, '/api/emails', $payload);
        $this->assertSingleEmailSentWithBodyContains($craftsman->getAuthenticationToken());

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiPostStatusCodeSame(Response::HTTP_FORBIDDEN, $client, '/api/emails', $payload);
    }
}
