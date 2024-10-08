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
use App\Tests\DataFixtures\TestFilterFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ApiAuthenticationTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testCannotAccessOrModifyExceptExceptions(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $otherConstructionSite = $this->getEmptyConstructionSite();

        $constructionManager = $this->getTestConstructionManager();
        $otherConstructionManager = $this->addConstructionManager($otherConstructionSite);

        $craftsman = $constructionSite->getCraftsmen()[0];
        $otherCraftsman = $this->addCraftsman($otherConstructionSite);

        $filter = $constructionSite->getFilters()[0];
        $otherFilter = $this->addFilter($otherConstructionSite);

        $issue = $constructionSite->getIssues()[0];
        $otherIssue = $this->addIssue($otherConstructionSite, $constructionManager);

        $map = $constructionSite->getMaps()[0];
        $otherMap = $this->addMap($otherConstructionSite);

        $constructionManagerToken = $this->createApiTokenFor($constructionManager);
        $craftsmanToken = $this->createApiTokenFor($craftsman);
        $filterToken = $this->createApiTokenFor($filter);

        $payloads = [
            'construction_managers' => [$constructionManager, $otherConstructionManager],
            'construction_sites' => [$constructionSite, $otherConstructionSite],
            'craftsmen' => [$craftsman, $otherCraftsman],
            'filters' => [$filter, $otherFilter],
            'issues' => [$issue, $otherIssue],
            'maps' => [$map, $otherMap],
        ];

        $noPost = ['construction_managers'];
        $noPatch = ['construction_sites', 'construction_managers', 'filters'];
        $noDelete = ['construction_sites', 'construction_managers', 'filters'];

        foreach ($payloads as $url => $payload) {
            $apiUrl = '/api/'.$url;
            $own = $payload[0];
            $other = $payload[1];

            $sameConstructionSiteId = $apiUrl.'/'.$own->getId();
            $otherConstructionSiteId = $apiUrl.'/'.$other->getId();

            $this->assertApiTokenRequestSuccessful($client, $constructionManagerToken, 'GET', $sameConstructionSiteId);
            if (in_array($url, ['construction_sites', 'construction_managers'])) {
                $this->assertApiTokenRequestSuccessful($client, $constructionManagerToken, 'GET', $otherConstructionSiteId);
            } else {
                $this->assertApiTokenRequestForbidden($client, $constructionManagerToken, 'GET', $otherConstructionSiteId);
            }

            if ('filters' === $url) {
                $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'GET', $sameConstructionSiteId);
            } else {
                $this->assertApiTokenRequestSuccessful($client, $craftsmanToken, 'GET', $sameConstructionSiteId);
            }
            $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'GET', $otherConstructionSiteId);
            if (!in_array($url, $noPost)) {
                $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'POST', $apiUrl, []);
            }
            if ('issues' !== $url && !in_array($url, $noPatch)) {
                $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'PATCH', $sameConstructionSiteId, []);
            }
            if (!in_array($url, $noDelete)) {
                $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'DELETE', $sameConstructionSiteId, []);
            }

            $this->assertApiTokenRequestSuccessful($client, $filterToken, 'GET', $sameConstructionSiteId);
            $this->assertApiTokenRequestForbidden($client, $filterToken, 'GET', $otherConstructionSiteId);
            if (!in_array($url, $noPost)) {
                $this->assertApiTokenRequestForbidden($client, $filterToken, 'POST', $apiUrl, []);
            }
            if (!in_array($url, $noPatch)) {
                $this->assertApiTokenRequestForbidden($client, $filterToken, 'PATCH', $sameConstructionSiteId, []);
            }
            if (!in_array($url, $noDelete)) {
                $this->assertApiTokenRequestForbidden($client, $filterToken, 'DELETE', $sameConstructionSiteId, []);
            }
        }
    }

    public function testApiAccessEmailTemplates(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class, TestEmailTemplateFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $otherConstructionSite = $this->getEmptyConstructionSite();

        $constructionManager = $this->getTestConstructionManager();

        $craftsman = $constructionSite->getCraftsmen()[0];
        $filter = $constructionSite->getFilters()[0];

        $emailTemplate = $constructionSite->getEmailTemplates()[0];
        $otherEmailTemplate = $this->addMap($otherConstructionSite);

        $constructionManagerToken = $this->createApiTokenFor($constructionManager);
        $craftsmanToken = $this->createApiTokenFor($craftsman);
        $filterToken = $this->createApiTokenFor($filter);

        $apiUrl = '/api/email_templates';
        $sameConstructionSiteId = $apiUrl.'/'.$emailTemplate->getId();
        $otherConstructionSiteId = $apiUrl.'/'.$otherEmailTemplate->getId();

        $this->assertApiTokenRequestSuccessful($client, $constructionManagerToken, 'GET', $sameConstructionSiteId);
        $this->assertApiTokenRequestNotFound($client, $constructionManagerToken, 'GET', $otherConstructionSiteId);

        $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'GET', $sameConstructionSiteId);
        $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'POST', $apiUrl, []);
        $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'PATCH', $sameConstructionSiteId);
        $this->assertApiTokenRequestForbidden($client, $craftsmanToken, 'DELETE', $sameConstructionSiteId);

        $this->assertApiTokenRequestForbidden($client, $filterToken, 'GET', $sameConstructionSiteId);
        $this->assertApiTokenRequestForbidden($client, $filterToken, 'POST', $apiUrl, []);
        $this->assertApiTokenRequestForbidden($client, $filterToken, 'PATCH', $sameConstructionSiteId);
        $this->assertApiTokenRequestForbidden($client, $filterToken, 'DELETE', $sameConstructionSiteId);
    }

    public function testFileAndImageDownload(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $testConstructionSite = $this->getTestConstructionSite();
        $constructionManager = $this->getTestConstructionManager();

        $craftsman = $testConstructionSite->getCraftsmen()[0];
        $filter = $testConstructionSite->getFilters()[0];

        $constructionManagerToken = $this->createApiTokenFor($constructionManager);
        $craftsmanToken = $this->createApiTokenFor($craftsman);
        $filterToken = $this->createApiTokenFor($filter);

        $this->setApiTokenDefaultHeader($client, $constructionManagerToken);

        $response = $this->assertApiGetOk($client, '/api/construction_sites');
        $this->assertApiResponseFileIsDownloadable($client, $response, 'imageUrl');

        foreach ([$constructionManagerToken, $craftsmanToken, $filterToken] as $token) {
            $this->setApiTokenDefaultHeader($client, $token);

            $response = $this->assertApiGetOk($client, '/api/maps?constructionSite='.$testConstructionSite->getId());
            $this->assertApiResponseFileIsDownloadable($client, $response, 'fileUrl', ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        }

        foreach ([$constructionManagerToken, $filterToken] as $token) {
            $this->setApiTokenDefaultHeader($client, $token);

            $response = $this->assertApiGetOk($client, '/api/issues?constructionSite='.$testConstructionSite->getId());
            $this->assertApiResponseFileIsDownloadable($client, $response, 'imageUrl');
        }

        $this->setApiTokenDefaultHeader($client, $craftsmanToken);

        $response = $this->assertApiGetOk($client, '/api/issues?constructionSite='.$testConstructionSite->getId().'&craftsman='.$craftsman->getId().'&isDeleted=false');
        $this->assertApiResponseFileIsDownloadable($client, $response, 'imageUrl');
    }

    public function testCraftsmanGetQueryEnforced(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $testConstructionSite = $this->getTestConstructionSite();
        $otherConstructionSite = $this->getEmptyConstructionSite();
        $craftsman = $testConstructionSite->getCraftsmen()[0];

        $craftsmanToken = $this->createApiTokenFor($craftsman);
        $this->setApiTokenDefaultHeader($client, $craftsmanToken);

        $constructionSiteCondition = 'constructionSite='.$testConstructionSite->getId();
        $otherConstructionSiteCondition = 'constructionSite='.$otherConstructionSite->getId();
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/construction_managers');
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/construction_managers?constructionSites.id='.$testConstructionSite->getId());
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/construction_sites');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/maps');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/maps?'.$otherConstructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/maps?'.$constructionSiteCondition);

        $otherRequiredProperties = ['craftsman='.$craftsman->getId(), 'isDeleted=false'];
        foreach ($otherRequiredProperties as $requiredProperty) {
            $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues?'.$requiredProperty);
        }
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues?'.$constructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues?'.implode('&', $otherRequiredProperties).'&'.$otherConstructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/issues?'.implode('&', $otherRequiredProperties).'&'.$constructionSiteCondition);
    }

    public function testFilterGetQueryEnforced(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $testConstructionSite = $this->getTestConstructionSite();
        $otherConstructionSite = $this->getEmptyConstructionSite();
        $filter = $testConstructionSite->getFilters()[0];

        $filterToken = $this->createApiTokenFor($filter);
        $this->setApiTokenDefaultHeader($client, $filterToken);

        $constructionSiteCondition = 'constructionSite='.$testConstructionSite->getId();
        $otherConstructionSiteCondition = 'constructionSite='.$otherConstructionSite->getId();
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/construction_managers');
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/construction_managers?constructionSites.id='.$testConstructionSite->getId());
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/construction_sites');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen?'.$otherConstructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/craftsmen?'.$constructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/maps');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/maps?'.$otherConstructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/maps?'.$constructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues?'.$otherConstructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/issues?'.$constructionSiteCondition);
    }

    public function testConstructionSiteGetQueryEnforced(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $testConstructionSite = $this->getTestConstructionSite();
        $otherConstructionSite = $this->getEmptyConstructionSite();
        $constructionManager = $this->getTestConstructionManager();

        $filterToken = $this->createApiTokenFor($constructionManager);
        $this->setApiTokenDefaultHeader($client, $filterToken);

        $constructionSiteCondition = 'constructionSite='.$testConstructionSite->getId();
        $otherConstructionSiteCondition = 'constructionSite='.$otherConstructionSite->getId();
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/construction_managers');
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/construction_sites');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/craftsmen?'.$otherConstructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/craftsmen?'.$constructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/maps');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/maps?'.$otherConstructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/maps?'.$constructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues');
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/issues?'.$otherConstructionSiteCondition);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/issues?'.$constructionSiteCondition);
    }

    public function testCraftsmanEdit(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

        $testConstructionSite = $this->getTestConstructionSite();
        $craftsman = $testConstructionSite->getCraftsmen()[0];
        $otherCraftsman = $testConstructionSite->getCraftsmen()[1];
        $otherCraftsmanIri = $this->getIriFromItem($otherCraftsman);
        $craftsmanIri = $this->getIriFromItem($craftsman);
        $craftsmanToken = $this->createApiTokenFor($craftsman);

        $this->setApiTokenDefaultHeader($client, $craftsmanToken);

        $issue = $craftsman->getIssues()[0];
        $issueIri = $this->getIriFromItem($issue);

        $otherConstructionManager = $this->getTestConstructionManager();
        $otherConstructionManagerId = $this->getIriFromItem($otherConstructionManager);

        $forbiddenUpdate = [
            'resolvedAt' => (new \DateTime('yesterday + 2 day'))->format('c'),
            'resolvedBy' => $otherCraftsmanIri,
        ];
        $this->assertApiPatchStatusCodeSame(Response::HTTP_FORBIDDEN, $client, $issueIri, $forbiddenUpdate);

        $update = [
            'resolvedAt' => (new \DateTime('yesterday + 2 day'))->format('c'),
            'resolvedBy' => $craftsmanIri,
        ];
        $response = $this->assertApiPatchPayloadPersisted($client, $issueIri, $update);
        $this->assertApiCollectionContainsResponseItem($client, '/api/issues?constructionSite='.$testConstructionSite->getId().'&craftsman='.$craftsman->getId().'&isDeleted=false', $response);

        $writeProtected = [
            'createdAt' => (new \DateTime('tomorrow'))->format('c'),
            'createdBy' => $otherConstructionManagerId,

            'description' => 'hello world 2',
            'wasAddedWithClient' => true,
            'isMarked' => true,
            'deadline' => (new \DateTime('yesterday'))->format('c'),

            'positionX' => 0.6,
            'positionY' => 0.7,
            'positionZoomScale' => 0.8,

            'registeredAt' => (new \DateTime('yesterday + 1 day'))->format('c'),
            'registeredBy' => $otherConstructionManagerId,

            'closedAt' => (new \DateTime('yesterday + 3 day'))->format('c'),
            'closedBy' => $otherConstructionManagerId,
        ];
        $this->assertApiPatchPayloadIgnored($client, $issueIri, $writeProtected);
    }
}
