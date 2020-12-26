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
use App\Tests\DataFixtures\TestEmailTemplateFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;

class EmailTemplateTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestEmailTemplateFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $this->assertApiOperationNotAuthorized($client, '/api/email_templates?constructionSite='.$constructionSite->getId(), 'GET', 'POST');
        $this->assertApiOperationNotAuthorized($client, '/api/email_templates/'.$constructionSite->getId(), 'GET', 'PATCH', 'DELETE');

        $this->loginApiConstructionManagerExternal($client);
        $this->assertApiOperationForbidden($client, '/api/email_templates', 'POST');
        $this->assertApiOperationForbidden($client, '/api/email_templates/'.$constructionSite->getEmailTemplates()[0]->getId(), 'GET', 'PATCH', 'DELETE');
    }

    public function testPostPatchAndDelete()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginApiConstructionManager($client);

        $constructionSite = $this->getTestConstructionSite();
        $constructionSiteId = $this->getIriFromItem($constructionSite);
        $affiliation = [
            'constructionSite' => $constructionSiteId,
        ];

        $sample = [
            'name' => 'Template 1',
            'subject' => 'Willkommen',
            'body' => 'Hallo auf der Baustelle 2',
            'purpose' => 1,
            'selfBcc' => false,
        ];

        $this->assertApiPostPayloadMinimal(Response::HTTP_BAD_REQUEST, $client, '/api/email_templates', $sample, $affiliation);
        $this->assertApiPostPayloadMinimal(Response::HTTP_FORBIDDEN, $client, '/api/email_templates', $affiliation, $sample);
        $response = $this->assertApiPostPayloadPersisted($client, '/api/email_templates', $sample, $affiliation);

        // test GET returns correct fields
        $this->assertApiCollectionContainsResponseItem($client, '/api/email_templates?constructionSite='.$constructionSite->getId(), $response);
        $this->assertApiResponseFieldSubset($response, 'name', 'subject', 'body', 'purpose', 'selfBcc');

        $emailTemplateId = json_decode($response->getContent(), true)['@id'];

        // test construction site can not be changed anymore
        $emptyConstructionSite = $this->getEmptyConstructionSite();
        $emptyConstructionSiteId = $this->getIriFromItem($emptyConstructionSite);
        $writeProtected = [
            'constructionSite' => $emptyConstructionSiteId,
        ];
        $this->assertApiPatchPayloadIgnored($client, $emailTemplateId, $writeProtected);

        // test PATCH applies changes
        $update = [
            'name' => 'Template 2',
            'subject' => 'Willkommen im Paradies',
            'body' => 'Hallo auf der Baustelle 3',
            'purpose' => 2,
            'selfBcc' => true,
        ];
        $response = $this->assertApiPatchPayloadPersisted($client, $emailTemplateId, $update);
        $this->assertApiCollectionContainsResponseItem($client, '/api/email_templates?constructionSite='.$constructionSite->getId(), $response);

        // test DELETE removes item
        $this->assertApiDeleteOk($client, $emailTemplateId);
        $this->assertApiCollectionNotContainsIri($client, '/api/email_templates?constructionSite='.$constructionSite->getId(), $emailTemplateId);
    }
}
