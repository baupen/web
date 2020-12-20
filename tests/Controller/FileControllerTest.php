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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AuthenticationTrait;
    use TestDataTrait;
    use AssertFileTrait;

    public function testMapFile()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginConstructionManager($client);

        $testConstructionSite = $this->getTestConstructionSite();
        $map = $testConstructionSite->getMaps()[0];
        $oldGuid = $map->getFile()->getId();

        $filename = '2OG_2.pdf';
        $uploadedFile = new AssetFile(__DIR__.'/../../assets/samples/Test/map_files/'.$filename);
        $baseUrl = '/maps/'.$map->getId().'/file';
        $url = $this->assertPostUploadFile($client, $baseUrl, $uploadedFile);

        $this->assertStringNotContainsString($oldGuid, $url);
        $this->assertStringContainsString($map->getFile()->getId(), $url);

        $this->assertGetFile($client, $url, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        $this->assertGetFile($client, $url.'?variant=ios', ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        $this->assertFileNotFound($client, $url.'?variant=undefined');
    }

    private function assertPostUploadFile(KernelBrowser $client, string $url, AssetFile $file)
    {
        $client->request('POST', $url, [], ['file' => $file]);

        $this->assertResponseStatusCodeSame(StatusCode::HTTP_CREATED);

        return $client->getResponse()->getContent();
    }
}
