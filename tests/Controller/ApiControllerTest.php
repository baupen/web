<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller;

use App\DataFixtures\Model\AssetFile;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertFileTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ApiControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AuthenticationTrait;
    use TestDataTrait;
    use AssertFileTrait;

    public function testConstructionSiteImage()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginConstructionManager($client);

        $testConstructionSite = $this->getTestConstructionSite();
        $image = $testConstructionSite->getImage();
        $oldGuid = $image->getId();

        $uploadedFile = new AssetFile(__DIR__.'/../../assets/samples/Test/preview_2.jpg');
        $baseUrl = '/api/construction_sites/'.$testConstructionSite->getId().'/image';
        $url = $this->assertPostFile($client, $baseUrl, $uploadedFile);

        $image = $testConstructionSite->getImage();
        $this->assertStringNotContainsString($oldGuid, $url);
        $this->assertStringContainsString($image->getId(), $url);

        $this->assertImageDownloads($client, $url);

        // try a second time
        $uploadedFile2 = new AssetFile(__DIR__.'/../../assets/samples/Test/preview.jpg');
        $url2 = $this->assertPostFile($client, $baseUrl, $uploadedFile2);

        $this->assertNotEquals($url, $url2);
        $this->assertSingleImageDownloads($client, $url2);
    }

    public function testIssueImage()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginConstructionManager($client);

        $testConstructionSite = $this->getTestConstructionSite();
        $issue = $testConstructionSite->getIssues()[0];
        $oldGuid = $issue->getImage()->getId();

        $uploadedFile = new AssetFile(__DIR__.'/../../assets/samples/Test/issue_images/nachbessern_2.jpg');
        $baseUrl = '/api/issues/'.$issue->getId().'/image';
        $url = $this->assertPostFile($client, $baseUrl, $uploadedFile);

        $this->assertStringNotContainsString($oldGuid, $url);
        $this->assertStringContainsString($issue->getImage()->getId(), $url);

        $this->assertImageDownloads($client, $url);

        // try a second time
        $uploadedFile2 = new AssetFile(__DIR__.'/../../assets/samples/Test/issue_images/nachbessern.jpg');
        $url2 = $this->assertPostFile($client, $baseUrl, $uploadedFile2);

        $this->assertNotEquals($url, $url2);
        $this->assertSingleImageDownloads($client, $url2);
    }

    public function testMapFile()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginConstructionManager($client);

        $testConstructionSite = $this->getTestConstructionSite();
        $map = $testConstructionSite->getMaps()[0];
        $oldGuid = $map->getFile()->getId();

        $uploadedFile = new AssetFile(__DIR__.'/../../assets/samples/Test/map_files/2OG_2.pdf');
        $baseUrl = '/api/maps/'.$map->getId().'/file';
        $url = $this->assertPostFile($client, $baseUrl, $uploadedFile);

        $this->assertStringNotContainsString($oldGuid, $url);
        $this->assertStringContainsString($map->getFile()->getId(), $url);

        $this->assertGetFile($client, $url, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        $this->assertGetFile($client, $url.'?variant=ios', ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        $this->assertFileNotFound($client, $url.'?variant=undefined');

        // try a second time
        $uploadedFile2 = new AssetFile(__DIR__.'/../../assets/samples/Test/map_files/2OG.pdf');
        $url2 = $this->assertPostFile($client, $baseUrl, $uploadedFile2);

        $this->assertNotEquals($url, $url2);
        $this->assertGetFile($client, $url2, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    private function assertImageDownloads(KernelBrowser $client, string $imageUrl): void
    {
        $this->assertGetFile($client, $imageUrl);
        $this->assertGetFile($client, $imageUrl.'?size=thumbnail');
        $this->assertGetFile($client, $imageUrl.'?size=preview');
        $this->assertGetFile($client, $imageUrl.'?size=full');
        $this->assertFileNotFound($client, $imageUrl.'?size=null');
    }

    private function assertSingleImageDownloads(KernelBrowser $client, string $imageUrl): void
    {
        $this->assertGetFile($client, $imageUrl);
    }
}
