<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
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

        $this->assertApiOperationUnsupported($client, '/api/craftsman_emails', 'GET');
        $this->assertApiOperationNotAuthorized($client, '/api/craftsman_emails/someid', 'GET');
        $this->assertApiOperationUnsupported($client, '/api/craftsman_emails/someid', 'PATCH');
        $this->assertApiOperationUnsupported($client, '/api/craftsman_emails/someid', 'DELETE');
        $this->assertApiOperationNotAuthorized($client, '/api/craftsman_emails', 'POST');
    }

    public function testPost(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsmanId = $this->getIriFromItem($craftsman);

        $affiliation = ['constructionSite' => $constructionSiteId];
        $payload = [
            'receiver' => $craftsmanId,
            'subject' => 'Willkommen',
            'body' => 'Hallo auf der Baustelle 2',
            'selfBcc' => false,
        ];

        $this->loginApiConstructionManager($client);
        $this->assertApiPostPayloadMinimal(Response::HTTP_UNPROCESSABLE_ENTITY, $client, '/api/craftsman_emails', $payload, $affiliation);
        $this->assertApiPostStatusCodeSame(Response::HTTP_CREATED, $client, '/api/craftsman_emails', array_merge($payload, $affiliation));
        $this->assertSingleEmailSentWithBodyContains($craftsman->getAuthenticationToken());

        $this->loginApiDisassociatedConstructionManager($client);
        $this->assertApiPostStatusCodeSame(Response::HTTP_FORBIDDEN, $client, '/api/craftsman_emails', array_merge($payload, $affiliation));
    }
}
