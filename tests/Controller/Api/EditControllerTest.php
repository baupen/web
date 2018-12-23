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

use App\Api\Entity\Edit\CheckMapFile;
use App\Api\Entity\Edit\UploadMapFile;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\Edit\CheckMapFileRequest;
use App\Api\Request\Edit\UploadMapFileRequest;
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

    public function testMapFileUpload()
    {
        $uploadUrl = '/api/edit/map_file';
        $checkUrl = '/api/edit/map_file/check';

        // prepare sample file2
        $filePath = __DIR__ . '/../../Files/sample.pdf';
        $copyPath = __DIR__ . '/../../Files/sample_2.pdf';
        copy($filePath, $copyPath);
        $copyPath2 = __DIR__ . '/../../Files/sample_3.pdf';
        copy($filePath, $copyPath2);

        // properties needed
        $hash = hash_file('sha256', $copyPath);
        $originalName = 'sample.pdf';
        $uploadFile = new UploadedFile($copyPath, $originalName, 'application/pdf');
        $uploadFile2 = new UploadedFile($copyPath2, $originalName, 'application/pdf');
        $constructionSite = $this->getSomeConstructionSite();

        // first request; expect no file like this at server yet
        $mapFile = new CheckMapFile();
        $mapFile->setHash($hash);
        $mapFile->setFilename($originalName);
        $checkMapFileRequest = new CheckMapFileRequest();
        $checkMapFileRequest->setMapFile($mapFile);
        $checkMapFileRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($checkUrl, $checkMapFileRequest);
        $uploadFileCheckData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // check correctly detected as new file
        $this->assertNotNull($uploadFileCheckData->data);
        $this->assertNotNull($uploadFileCheckData->data->uploadFileCheck);
        $uploadFileCheck = $uploadFileCheckData->data->uploadFileCheck;
        $this->assertTrue($uploadFileCheck->uploadPossible);
        $this->assertEmpty($uploadFileCheck->sameHashConflicts);
        $this->assertNull($uploadFileCheck->fileNameConflict);
        $this->assertSame($originalName, $uploadFileCheck->derivedFileName);

        // second request; upload file
        $mapFile = new UploadMapFile();
        $mapFile->setFilename($originalName);
        $uploadMapFileRequest = new UploadMapFileRequest();
        $uploadMapFileRequest->setMapFile($mapFile);
        $uploadMapFileRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($uploadUrl, $uploadMapFileRequest, ['some_file' => $uploadFile]);
        $uploadFileCheckData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // check uploaded as expected
        $this->assertNotNull($uploadFileCheckData->data);
        $this->assertNotNull($uploadFileCheckData->data->mapFile);
        $mapFile = $uploadFileCheckData->data->mapFile;
        $this->assertObjectHasAttribute('id', $mapFile);
        $this->assertSame($originalName, $mapFile->filename);
        $this->assertNotNull($mapFile->createdAt);
        $this->assertNull($mapFile->mapId);
        $this->assertTrue($mapFile->issueCount === 0);

        // third request; expect exact file like this at server
        $response = $this->authenticatedPostRequest($checkUrl, $checkMapFileRequest);
        $uploadFileCheckData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // check correctly detected as new file
        $this->assertNotNull($uploadFileCheckData->data);
        $this->assertNotNull($uploadFileCheckData->data->uploadFileCheck);
        $uploadFileCheck = $uploadFileCheckData->data->uploadFileCheck;
        $this->assertTrue($uploadFileCheck->uploadPossible);
        $this->assertNotEmpty($uploadFileCheck->sameHashConflicts);
        $this->assertTrue(\count($uploadFileCheck->sameHashConflicts) === 1 && $uploadFileCheck->sameHashConflicts[0] === $mapFile->id);
        $this->assertNotNull($uploadFileCheck->fileNameConflict);
        $this->assertTrue($uploadFileCheck->fileNameConflict === $mapFile->id);
        $this->assertNotSame($originalName, $uploadFileCheck->derivedFileName);

        // fourth request; expect upload is denied
        $response = $this->authenticatedPostRequest($uploadUrl, $uploadMapFileRequest, ['some_file' => $uploadFile2]);
        $this->checkResponse($response, ApiStatus::FAIL, 'map file could not be uploaded');

        // cleanup file
        unlink($copyPath2);
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
