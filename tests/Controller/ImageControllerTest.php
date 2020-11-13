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

class ImageControllerTest extends WebTestCase
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
        $baseUrl = '/construction_sites/'.$testConstructionSite->getId().'/image';
        $newGuid = $this->assertPostFile($client, $baseUrl, $uploadedFile);

        $image = $testConstructionSite->getImage();
        $this->assertNotEquals($oldGuid, $newGuid);
        $this->assertEquals($image->getId(), $newGuid);

        $imageUrl = $baseUrl.'/'.$newGuid;
        $this->assertImageDownloads($client, $imageUrl);
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
        $baseUrl = '/issues/'.$issue->getId().'/image';
        $newGuid = $this->assertPostFile($client, $baseUrl, $uploadedFile);

        $this->assertNotEquals($oldGuid, $newGuid);
        $this->assertEquals($issue->getImage()->getId(), $newGuid);

        $imageUrl = $baseUrl.'/'.$newGuid;
        $this->assertImageDownloads($client, $imageUrl);
    }

    private function assertImageDownloads(KernelBrowser $client, string $imageUrl): void
    {
        $this->assertGetFile($client, $imageUrl);
        $this->assertGetFile($client, $imageUrl.'/thumbnail');
        $this->assertGetFile($client, $imageUrl.'/preview');
        $this->assertGetFile($client, $imageUrl.'/full');
        $this->assertFileNotFound($client, $imageUrl.'/null');
    }
}
