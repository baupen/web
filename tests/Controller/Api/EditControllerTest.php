<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api;

use App\Api\Request\ConstructionSiteRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EditControllerTest extends ApiController
{
    public function testMapFiles()
    {
        $url = '/api/edit/map_files';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $mapFilesData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapFilesData->data);
        $this->assertTrue(\is_array($mapFilesData->data->mapFiles));
        $this->assertTrue(\count($mapFilesData->data->mapFiles) > 0);
        foreach ($mapFilesData->data->mapFiles as $mapFile) {
            $this->assertNotNull($mapFile);
            $this->assertObjectHasAttribute('id', $mapFile);
            $this->assertObjectHasAttribute('filename', $mapFile);
            $this->assertObjectHasAttribute('createdAt', $mapFile);
            $this->assertObjectHasAttribute('mapId', $mapFile);
            $this->assertObjectHasAttribute('issueCount', $mapFile);
        }
    }

    public function testMapFilePost()
    {
        $url = '/api/edit/map_file';

        $filePath = __DIR__ . '/../../Files/sample.pdf';
        $copyPath = __DIR__ . '/../../Files/sample_2.pdf';
        $originalName = "sample.pdf";
        copy($filePath, $copyPath);
        $file = new UploadedFile(
            $copyPath,
            $originalName,
            'application/pdf'
        );

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest, ["some_file" => $file]);
        $mapFileData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapFileData->data);
        $this->assertNotNull($mapFileData->data->mapFile);

        $mapFile = $mapFileData->data->mapFile;
        $this->assertObjectHasAttribute('id', $mapFile);
        $this->assertEquals($originalName, $mapFile->filename);
        $this->assertNotNull($mapFile->createdAt);
        $this->assertNull($mapFile->mapId);
        $this->assertTrue($mapFile->issueCount === 0);

    }

    public function testMaps()
    {
        $url = '/api/edit/maps';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapData->data);
        $this->assertTrue(\is_array($mapData->data->maps));
        $this->assertTrue(\count($mapData->data->maps) > 0);
        foreach ($mapData->data->maps as $map) {
            $this->assertNotNull($map);
            $this->assertObjectHasAttribute('id', $map);
            $this->assertObjectHasAttribute('name', $map);
            $this->assertObjectHasAttribute('fileId', $map);
            $this->assertObjectHasAttribute('parentId', $map);
            $this->assertObjectHasAttribute('createdAt', $map);
            $this->assertObjectHasAttribute('issueCount', $map);
        }
    }

    public function testCraftsmen()
    {
        $url = '/api/edit/craftsmen';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $craftsmenData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($craftsmenData->data);
        $this->assertTrue(\is_array($craftsmenData->data->craftsmen));
        $this->assertTrue(\count($craftsmenData->data->craftsmen) > 0);
        foreach ($craftsmenData->data->craftsmen as $craftsman) {
            $this->assertNotNull($craftsman);
            $this->assertObjectHasAttribute('id', $craftsman);
            $this->assertObjectHasAttribute('contactName', $craftsman);
            $this->assertObjectHasAttribute('company', $craftsman);
            $this->assertObjectHasAttribute('email', $craftsman);
            $this->assertObjectHasAttribute('issueCount', $craftsman);
        }
    }
}
