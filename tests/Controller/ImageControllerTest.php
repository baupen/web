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
use Symfony\Component\HttpFoundation\Response as StatusCode;

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
        $oldGuid = $testConstructionSite->getImage()->getId();

        $uploadedFile = new AssetFile(__DIR__.'/../../assets/samples/Test/preview_2.jpg');
        $baseUrl = '/construction_sites/'.$testConstructionSite->getId().'/image';
        $newGuid = $this->assertPostUploadFile($client, $baseUrl, $uploadedFile);

        $this->assertNotEquals($oldGuid, $newGuid);
        $this->assertEquals($testConstructionSite->getImage()->getId(), $newGuid);

        $imageUrl = $baseUrl.'/'.$newGuid;
        $this->assertFileIsDownloadable($client, $imageUrl);
        $this->assertFileIsDownloadable($client, $imageUrl.'/thumbnail');
        $this->assertFileIsDownloadable($client, $imageUrl.'/preview');
        $this->assertFileIsDownloadable($client, $imageUrl.'/full');
        $this->assertFileNotFound($client, $imageUrl.'/null');
    }

    private function assertPostUploadFile(KernelBrowser $client, string $url, AssetFile $file)
    {
        $client->request('POST', $url, [], ['file' => $file]);

        $this->assertResponseStatusCodeSame(StatusCode::HTTP_OK);

        return $client->getResponse()->getContent();
    }
}
