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
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Model\AssetFile;
use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\Map;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\DataFixtures\TestFilterFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AssertFileTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ApiControllerTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;
    use TestDataTrait;
    use AssertFileTrait;

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $this->assertApiOperationNotAuthorized($client, '/api/me', 'GET');
    }

    public function testMe()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class, TestFilterFixtures::class]);

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

    public function testConstructionSiteImage()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginConstructionManager($client->getKernelBrowser());

        $testConstructionSite = $this->getTestConstructionSite();
        $image = $testConstructionSite->getImage();
        $oldGuid = $image->getId();

        $uploadedFile = new AssetFile(__DIR__.'/../../assets/samples/Test/preview_2.jpg');
        $baseUrl = '/api/construction_sites/'.$testConstructionSite->getId().'/image';
        $url = $this->assertApiPostFile($client->getKernelBrowser(), $baseUrl, $uploadedFile);

        $image = $testConstructionSite->getImage();
        $this->assertStringNotContainsString($oldGuid, $url);
        $this->assertStringContainsString($image->getId(), $url);

        $client = $this->createClient(); // logout
        $this->assertImageDownloads($client, $url);

        // try a second time
        $this->loginConstructionManager($client->getKernelBrowser());
        $uploadedFile2 = new AssetFile(__DIR__.'/../../assets/samples/Test/preview.jpg');
        $url2 = $this->assertApiPostFile($client->getKernelBrowser(), $baseUrl, $uploadedFile2);

        $this->assertNotEquals($url, $url2);
        $this->assertSingleImageDownloads($client->getKernelBrowser(), $url2);

        // try delete
        $this->assertApiDeleteFile($client->getKernelBrowser(), $baseUrl);
        /** @var ConstructionSite $testConstructionSite */
        $testConstructionSite = $this->reloadEntity($testConstructionSite);
        $this->assertNull($testConstructionSite->getImage());
    }

    public function testIssueImage()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginConstructionManager($client->getKernelBrowser());

        $testConstructionSite = $this->getTestConstructionSite();
        $issue = $testConstructionSite->getIssues()[0];
        $oldGuid = $issue->getImage()->getId();

        $uploadedFile = new AssetFile(__DIR__.'/../../assets/samples/Test/issue_images/nachbessern_2.jpg');
        $baseUrl = '/api/issues/'.$issue->getId().'/image';
        $url = $this->assertApiPostFile($client->getKernelBrowser(), $baseUrl, $uploadedFile);

        $this->assertStringNotContainsString($oldGuid, $url);
        $this->assertStringContainsString($issue->getImage()->getId(), $url);

        $client = $this->createClient(); // logout
        $this->assertImageDownloads($client, $url);

        // try a second time
        $this->loginConstructionManager($client->getKernelBrowser());
        $uploadedFile2 = new AssetFile(__DIR__.'/../../assets/samples/Test/issue_images/nachbessern.jpg');
        $url2 = $this->assertApiPostFile($client->getKernelBrowser(), $baseUrl, $uploadedFile2);

        $this->assertNotEquals($url, $url2);
        $this->assertSingleImageDownloads($client->getKernelBrowser(), $url2);

        // try delete
        $this->assertApiDeleteFile($client->getKernelBrowser(), $baseUrl);
        /** @var Issue $issue */
        $issue = $this->reloadEntity($issue);
        $this->assertNull($issue->getImage());
    }

    public function testMapFile()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginConstructionManager($client->getKernelBrowser());

        $testConstructionSite = $this->getTestConstructionSite();
        $map = $testConstructionSite->getMaps()[0];
        $oldGuid = $map->getFile()->getId();

        $uploadedFile = new AssetFile(__DIR__.'/../../assets/samples/Test/map_files/2OG_2.pdf');
        $baseUrl = '/api/maps/'.$map->getId().'/file';
        $url = $this->assertApiPostFile($client->getKernelBrowser(), $baseUrl, $uploadedFile);

        $this->assertStringNotContainsString($oldGuid, $url);
        $this->assertStringContainsString($map->getFile()->getId(), $url);

        $client = $this->createClient();
        $this->assertGetFile($client->getKernelBrowser(), $url, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        $this->assertGetFile($client->getKernelBrowser(), $url.'?variant=ios', ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        $this->assertFileNotFound($client->getKernelBrowser(), $url.'?variant=undefined');

        // try a second time
        $this->loginConstructionManager($client->getKernelBrowser());
        $uploadedFile2 = new AssetFile(__DIR__.'/../../assets/samples/Test/map_files/2OG.pdf');
        $url2 = $this->assertApiPostFile($client->getKernelBrowser(), $baseUrl, $uploadedFile2);

        $this->assertNotEquals($url, $url2);
        $this->assertGetFile($client->getKernelBrowser(), $url2, ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        // try delete
        $this->assertApiDeleteFile($client->getKernelBrowser(), $baseUrl);
        /** @var Map $map */
        $map = $this->reloadEntity($map);
        $this->assertNull($map->getFile());
    }

    private function assertImageDownloads(Client $client, string $imageUrl): void
    {
        $this->assertGetFile($client->getKernelBrowser(), $imageUrl);
        $this->assertGetFile($client->getKernelBrowser(), $imageUrl.'?size=thumbnail');
        $this->assertGetFile($client->getKernelBrowser(), $imageUrl.'?size=preview');
        $this->assertGetFile($client->getKernelBrowser(), $imageUrl.'?size=full');
        $this->assertFileNotFound($client->getKernelBrowser(), $imageUrl.'?size=null');
    }

    private function assertSingleImageDownloads(KernelBrowser $client, string $imageUrl): void
    {
        $this->assertGetFile($client, $imageUrl);
    }
}
