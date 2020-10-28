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
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\DataFixtures\TestUserFixtures;
use App\Tests\Traits\AssertAuthenticationTrait;
use App\Tests\Traits\AssertEmailTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response as StatusCode;

class ImageControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AssertEmailTrait;
    use AssertAuthenticationTrait;
    use AuthenticationTrait;
    use TestDataTrait;

    public function testPostImage()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestUserFixtures::class, TestConstructionSiteFixtures::class]);
        $this->loginTestUser($client);

        $testConstructionSite = $this->getTestConstructionSite();
        $oldGuid = $testConstructionSite->getImage()->getId();

        $uploadedFile = new AssetFile(__DIR__.'/../../assets/samples/Test/preview_2.jpg');
        $newGuid = $this->assertPostUploadFile($client, '/construction_sites/'.$testConstructionSite->getId().'/image', $uploadedFile);

        $this->assertNotEquals($oldGuid, $newGuid);
        $this->assertEquals($testConstructionSite->getImage()->getId(), $newGuid);
    }

    private function assertPostUploadFile(KernelBrowser $client, string $url, AssetFile $file)
    {
        $client->request('POST', $url, [], ['file' => $file]);

        $this->assertResponseStatusCodeSame(StatusCode::HTTP_OK);

        return $client->getResponse()->getContent();
    }
}
