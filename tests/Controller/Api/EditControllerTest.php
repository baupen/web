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
use App\Api\Entity\Edit\UpdateConstructionSite;
use App\Api\Entity\Edit\UpdateCraftsman;
use App\Api\Entity\Edit\UpdateExternalConstructionManager;
use App\Api\Entity\Edit\UpdateMap;
use App\Api\Entity\Edit\UpdateMapFile;
use App\Api\Entity\Edit\UploadMapFile;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\Edit\CheckMapFileRequest;
use App\Api\Request\Edit\UpdateConstructionSiteRequest;
use App\Api\Request\Edit\UpdateCraftsmanRequest;
use App\Api\Request\Edit\UpdateExternalConstructionManagerRequest;
use App\Api\Request\Edit\UpdateMapFileRequest;
use App\Api\Request\Edit\UpdateMapRequest;
use App\Api\Request\Edit\UploadMapFileRequest;
use App\Entity\ConstructionSite;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;
use function count;
use function is_array;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EditControllerTest extends ApiController
{
    public function testConstructionSite()
    {
        $url = '/api/edit/construction_site';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $constructionSiteData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($constructionSiteData->data);
        $this->assertNotNull($constructionSiteData->data->constructionSite);

        $constructionSite = $constructionSiteData->data->constructionSite;
        $this->assertObjectHasAttribute('id', $constructionSite);
        $this->assertObjectHasAttribute('postalCode', $constructionSite);
        $this->assertObjectHasAttribute('streetAddress', $constructionSite);
        $this->assertObjectHasAttribute('locality', $constructionSite);
        $this->assertObjectHasAttribute('imageMedium', $constructionSite);
    }

    public function testConstructionSiteSave()
    {
        $saveUrl = '/api/edit/construction_site/save';
        $imageUrl = '/api/edit/construction_site/image';

        $constructionSite = $this->getSomeConstructionSite();
        $originalConstructionSite = $this->getConstructionSite($constructionSite->getId());

        // update properties
        $updateConstructionSiteRequest = new UpdateConstructionSiteRequest();
        $updateConstructionSite = new UpdateConstructionSite();
        $updateConstructionSite->setLocality("Basel");
        $updateConstructionSite->setPostalCode(4060);
        $updateConstructionSite->setStreetAddress("Ochsengasse 160");
        $updateConstructionSiteRequest->setConstructionSiteId($constructionSite->getId());
        $updateConstructionSiteRequest->setConstructionSite($updateConstructionSite);

        $response = $this->authenticatedPutRequest($saveUrl, $updateConstructionSiteRequest);
        $this->checkResponse($response, ApiStatus::SUCCESS);

        // update image
        $filePath = __DIR__ . '/../../Files/sample.jpg';
        $copyPath = __DIR__ . '/../../Files/sample_2.jpg';
        copy($filePath, $copyPath);

        // properties needed
        $originalName = 'sample.jpg';
        $uploadFile = new UploadedFile($copyPath, $originalName, 'image/jpeg');
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedPostRequest($imageUrl, $constructionSiteRequest, ['some_file' => $uploadFile]);
        $this->checkResponse($response, ApiStatus::SUCCESS);

        // check new image & properties were applied
        $newConstructionSite = $this->getConstructionSite($constructionSite->getId());

        $this->assertEquals($updateConstructionSite->getStreetAddress(), $newConstructionSite->streetAddress);
        $this->assertEquals($updateConstructionSite->getPostalCode(), $newConstructionSite->postalCode);
        $this->assertEquals($updateConstructionSite->getLocality(), $newConstructionSite->locality);
        $this->assertNotEquals($originalConstructionSite->imageMedium, $newConstructionSite->imageMedium);
    }

    public function testMapFiles()
    {
        $url = '/api/edit/map_files';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $mapFilesData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapFilesData->data);
        $this->assertTrue(is_array($mapFilesData->data->mapFiles));
        $this->assertTrue(count($mapFilesData->data->mapFiles) > 0);
        foreach ($mapFilesData->data->mapFiles as $mapFile) {
            $this->assertNotNull($mapFile);
            $this->assertObjectHasAttribute('id', $mapFile);
            $this->assertObjectHasAttribute('filename', $mapFile);
            $this->assertObjectHasAttribute('createdAt', $mapFile);
            $this->assertObjectHasAttribute('mapId', $mapFile);
            $this->assertObjectHasAttribute('issueCount', $mapFile);
        }
    }

    public function testMapFileUpdate()
    {
        $editUrl = '/api/edit/map_file';

        $constructionSite = $this->getSomeConstructionSite();
        $mapFile = $constructionSite->getMaps()[0]->getFile();
        $newMap = $constructionSite->getMaps()[1];

        // do request
        $updateMapFileRequest = new UpdateMapFileRequest();
        $updateMapFile = new UpdateMapFile();
        $updateMapFile->setMapId($newMap->getId());
        $updateMapFileRequest->setConstructionSiteId($constructionSite->getId());
        $updateMapFileRequest->setMapFile($updateMapFile);

        $response = $this->authenticatedPutRequest($editUrl . '/' . $mapFile->getId(), $updateMapFileRequest);
        $mapFileData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // ensure map id has been set properly
        $this->assertNotNull($mapFileData->data);
        $this->assertNotNull($mapFileData->data->mapFile);
        $this->assertTrue($mapFileData->data->mapFile->mapId === $newMap->getId());
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
        $this->assertTrue(count($uploadFileCheck->sameHashConflicts) === 1 && $uploadFileCheck->sameHashConflicts[0] === $mapFile->id);
        $this->assertNotNull($uploadFileCheck->fileNameConflict);
        $this->assertTrue($uploadFileCheck->fileNameConflict === $mapFile->id);
        $this->assertNotSame($originalName, $uploadFileCheck->derivedFileName);

        // fourth request; expect upload is denied
        $response = $this->authenticatedPostRequest($uploadUrl, $uploadMapFileRequest, ['some_file' => $uploadFile2]);
        $this->checkResponse($response, ApiStatus::FAIL, 'map file could not be uploaded');

        // cleanup file
        unlink($copyPath2);
    }

    public function testMapAdd()
    {
        $addUrl = '/api/edit/map';
        $availableMaps = $this->countAvailableMaps();
        $constructionSite = $this->getSomeConstructionSite();
        $parentMap = $constructionSite->getMaps()[0];

        // do request
        $updateMap = new UpdateMap();
        $updateMap->setName('new map');
        $updateMap->setParentId($parentMap->getId());
        $updateMapRequest = new UpdateMapRequest();
        $updateMapRequest->setMap($updateMap);
        $updateMapRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedPostRequest($addUrl, $updateMapRequest);
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // ensure map id has been set properly
        $this->assertNotNull($mapData->data);
        $this->assertNotNull($mapData->data->map);
        $map = $mapData->data->map;
        $this->assertNotNull($map->id);

        $this->assertSame($availableMaps + 1, $this->countAvailableMaps());
        $this->assertSame($updateMap->getName(), $map->name);
        $this->assertSame($updateMap->getParentId(), $map->parentId);
        $this->assertSame($updateMap->getFileId(), $map->fileId);
    }

    public function testMapUpdate()
    {
        $editUrl = '/api/edit/map';
        $availableMaps = $this->countAvailableMaps();
        $constructionSite = $this->getSomeConstructionSite();
        $someMap = $constructionSite->getMaps()[0];

        // do request
        $updateMap = new UpdateMap();
        $updateMap->setName($someMap->getName() . ' new');
        $updateMap->setParentId(null);
        $updateMap->setFileId(null);
        $updateMapRequest = new UpdateMapRequest();
        $updateMapRequest->setMap($updateMap);
        $updateMapRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedPutRequest($editUrl . '/' . $someMap->getId(), $updateMapRequest);
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // ensure map id has been set properly
        $this->assertNotNull($mapData->data);
        $this->assertNotNull($mapData->data->map);
        $map = $mapData->data->map;
        $this->assertNotNull($map->id);

        $this->assertSame($availableMaps, $this->countAvailableMaps());
        $this->assertSame($updateMap->getName(), $map->name);
        $this->assertSame($updateMap->getParentId(), $map->parentId);
        $this->assertSame($updateMap->getFileId(), $map->fileId);
    }

    public function testMapRemove()
    {
        $deleteUrl = '/api/edit/map';
        $availableMaps = $this->countAvailableMaps();
        $constructionSite = $this->getSomeConstructionSite();
        $someMap = $constructionSite->getMaps()[0];

        // add empty map to remove
        $updateMap = new UpdateMap();
        $updateMap->setName($someMap->getName() . ' new');
        $updateMapRequest = new UpdateMapRequest();
        $updateMapRequest->setMap($updateMap);
        $updateMapRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedPostRequest($deleteUrl, $updateMapRequest);
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // ensure map has really been added
        $this->assertSame($availableMaps + 1, $this->countAvailableMaps());
        $mapId = $mapData->data->map->id;

        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedDeleteRequest($deleteUrl . '/' . $mapId, $constructionSiteRequest);
        $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertSame($availableMaps, $this->countAvailableMaps());

        $testsExecuted = [false, false];
        foreach ($constructionSite->getMaps() as $map) {
            if ($testsExecuted[0] && $testsExecuted[1]) {
                break;
            }

            //test that map with issues can not be removed
            if (!$testsExecuted[1] && $map->getIssues()->count() > 0) {
                $response = $this->authenticatedDeleteRequest($deleteUrl . '/' . $map->getId(), $updateMapRequest);
                $this->checkResponse($response, ApiStatus::FAIL, 'map can not be removed as there are issues assigned to it');

                $testsExecuted[1] = true;
            }

            //test that map with children can not be removed
            if (!$testsExecuted[0] && $map->getChildren()->count() > 0 && $map->getIssues()->count() === 0) {
                $response = $this->authenticatedDeleteRequest($deleteUrl . '/' . $map->getId(), $updateMapRequest);
                $this->checkResponse($response, ApiStatus::FAIL, 'map can not be removed as there are children assigned to it');

                $testsExecuted[0] = true;
            }
        }

        //fail if not both safety checks could be executed with the testset
        if (!$testsExecuted[0] || !$testsExecuted[1]) {
            $this->fail('test set does not cover all needed cases');
        }
    }

    private function countAvailableMaps()
    {
        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest('/api/edit/maps', $constructionSiteRequest);

        return count($this->checkResponse($response, ApiStatus::SUCCESS)->data->maps);
    }

    public function testCraftsmanAdd()
    {
        $addUrl = '/api/edit/craftsman';
        $availableCraftsmen = $this->countAvailableCraftsmen();
        $constructionSite = $this->getSomeConstructionSite();

        // do request
        $updateCraftsman = new UpdateCraftsman();
        $updateCraftsman->setEmail('craft@man.ch');
        $updateCraftsman->setCompany('company');
        $updateCraftsman->setContactName('contact name');
        $updateCraftsman->setTrade('trade');
        $updateCraftsmanRequest = new UpdateCraftsmanRequest();
        $updateCraftsmanRequest->setCraftsman($updateCraftsman);
        $updateCraftsmanRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedPostRequest($addUrl, $updateCraftsmanRequest);
        $craftsmanData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // ensure craftsman id has been set properly
        $this->assertNotNull($craftsmanData->data);
        $this->assertNotNull($craftsmanData->data->craftsman);
        $craftsman = $craftsmanData->data->craftsman;
        $this->assertNotNull($craftsman->id);

        $this->assertSame($availableCraftsmen + 1, $this->countAvailableCraftsmen());
        $this->assertSame($updateCraftsman->getCompany(), $craftsman->company);
        $this->assertSame($updateCraftsman->getEmail(), $craftsman->email);
        $this->assertSame($updateCraftsman->getContactName(), $craftsman->contactName);
        $this->assertSame($updateCraftsman->getTrade(), $craftsman->trade);
        $this->assertSame(0, $craftsman->issueCount);
    }

    public function testCraftsmanUpdate()
    {
        $editUrl = '/api/edit/craftsman';
        $availableCraftsmen = $this->countAvailableCraftsmen();
        $constructionSite = $this->getSomeConstructionSite();
        $someCraftsman = $constructionSite->getCraftsmen()[0];

        // do request
        $updateCraftsman = new UpdateCraftsman();
        $updateCraftsman->setContactName($someCraftsman->getName() . ' new');
        $updateCraftsman->setCompany($someCraftsman->getCompany() . ' GmbH');
        $updateCraftsman->setEmail($someCraftsman->getEmail() . '.ch');
        $updateCraftsman->setTrade('professional ' . $someCraftsman->getTrade());
        $updateCraftsmanRequest = new UpdateCraftsmanRequest();
        $updateCraftsmanRequest->setCraftsman($updateCraftsman);
        $updateCraftsmanRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedPutRequest($editUrl . '/' . $someCraftsman->getId(), $updateCraftsmanRequest);
        $craftsmanData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // ensure craftsman id has been set properly
        $this->assertNotNull($craftsmanData->data);
        $this->assertNotNull($craftsmanData->data->craftsman);
        $craftsman = $craftsmanData->data->craftsman;
        $this->assertNotNull($craftsman->id);

        $this->assertSame($availableCraftsmen, $this->countAvailableCraftsmen());
        $this->assertSame($updateCraftsman->getCompany(), $craftsman->company);
        $this->assertSame($updateCraftsman->getEmail(), $craftsman->email);
        $this->assertSame($updateCraftsman->getContactName(), $craftsman->contactName);
        $this->assertSame($updateCraftsman->getTrade(), $craftsman->trade);
    }

    public function testCraftsmanRemove()
    {
        $deleteUrl = '/api/edit/craftsman';
        $availableCraftsmen = $this->countAvailableCraftsmen();
        $constructionSite = $this->getSomeConstructionSite();
        $someCraftsman = $constructionSite->getCraftsmen()[0];

        // add empty craftsman to remove
        $updateCraftsman = new UpdateCraftsman();
        $updateCraftsman->setContactName($someCraftsman->getName() . ' new');
        $updateCraftsman->setCompany($someCraftsman->getCompany() . ' GmbH');
        $updateCraftsman->setEmail($someCraftsman->getEmail() . '.ch');
        $updateCraftsman->setTrade('professional ' . $someCraftsman->getTrade());
        $updateCraftsmanRequest = new UpdateCraftsmanRequest();
        $updateCraftsmanRequest->setCraftsman($updateCraftsman);
        $updateCraftsmanRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedPostRequest($deleteUrl, $updateCraftsmanRequest);
        $craftsmanData = $this->checkResponse($response, ApiStatus::SUCCESS);

        // ensure craftsman has really been added
        $this->assertSame($availableCraftsmen + 1, $this->countAvailableCraftsmen());
        $craftsmanId = $craftsmanData->data->craftsman->id;

        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedDeleteRequest($deleteUrl . '/' . $craftsmanId, $constructionSiteRequest);
        $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertSame($availableCraftsmen, $this->countAvailableCraftsmen());

        $testsExecuted = false;
        foreach ($constructionSite->getCraftsmen() as $craftsman) {
            //test that craftsman with issues can not be removed
            if ($craftsman->getIssues()->count() > 0) {
                $response = $this->authenticatedDeleteRequest($deleteUrl . '/' . $craftsman->getId(), $updateCraftsmanRequest);
                $this->checkResponse($response, ApiStatus::FAIL, 'craftsman can not be removed as there are issues assigned to it');

                $testsExecuted = true;
                break;
            }
        }

        //fail if not both safety checks could be executed with the testset
        if (!$testsExecuted) {
            $this->fail('test set does not cover all needed cases');
        }
    }

    public function testExternalConstructionManagers()
    {
        // add
        $addUrl = '/api/edit/external_construction_manager';
        $testEmail = 'craft@man.ch';
        $constructionSite = $this->getSomeConstructionSite();

        $updateExternalConstructionManager = new UpdateExternalConstructionManager();
        $updateExternalConstructionManager->setEmail('craft@man.ch');
        $updateCraftsmanRequest = new UpdateExternalConstructionManagerRequest();
        $updateCraftsmanRequest->setExternalConstructionManager($updateExternalConstructionManager);
        $updateCraftsmanRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedPostRequest($addUrl, $updateCraftsmanRequest);
        $externalConstructionManagerData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($externalConstructionManagerData->data);
        $this->assertNotNull($externalConstructionManagerData->data->externalConstructionManager);
        $externalConstructionManager = $externalConstructionManagerData->data->externalConstructionManager;
        $this->assertNotNull($externalConstructionManager->id);
        $externalConstructionManagerId = $externalConstructionManager->id;

        // view
        $viewUrl = '/api/edit/external_construction_managers';
        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($viewUrl, $constructionSiteRequest);
        $externalConstructionManagerData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($externalConstructionManagerData->data);
        $this->assertTrue(is_array($externalConstructionManagerData->data->externalConstructionManagers));
        $externalConstructionManagerCount = count($externalConstructionManagerData->data->externalConstructionManagers) ;
        $this->assertTrue($externalConstructionManagerCount > 0);
        foreach ($externalConstructionManagerData->data->externalConstructionManagers as $externalConstructionManager) {
            $this->assertNotNull($externalConstructionManager);
            $this->assertObjectHasAttribute('id', $externalConstructionManager);
            $this->assertObjectHasAttribute('email', $externalConstructionManager);
        }

        // delete
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());
        $response = $this->authenticatedDeleteRequest($addUrl . '/' . $externalConstructionManagerId, $constructionSiteRequest);
        $this->checkResponse($response, ApiStatus::SUCCESS);

        // check really deleted
        $response = $this->authenticatedPostRequest($viewUrl, $constructionSiteRequest);
        $externalConstructionManagerData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($externalConstructionManagerData->data);
        $this->assertTrue(is_array($externalConstructionManagerData->data->externalConstructionManagers));
        $newExternalConstructionManagerCount = count($externalConstructionManagerData->data->externalConstructionManagers) ;
        $this->assertTrue($newExternalConstructionManagerCount + 1 == $externalConstructionManagerCount);

    }

    private function countAvailableCraftsmen()
    {
        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest('/api/edit/craftsmen', $constructionSiteRequest);

        return count($this->checkResponse($response, ApiStatus::SUCCESS)->data->craftsmen);
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
        $this->assertTrue(is_array($mapData->data->maps));
        $this->assertTrue(count($mapData->data->maps) > 0);
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
        $this->assertTrue(is_array($craftsmenData->data->craftsmen));
        $this->assertTrue(count($craftsmenData->data->craftsmen) > 0);
        foreach ($craftsmenData->data->craftsmen as $craftsman) {
            $this->assertNotNull($craftsman);
            $this->assertObjectHasAttribute('id', $craftsman);
            $this->assertObjectHasAttribute('contactName', $craftsman);
            $this->assertObjectHasAttribute('company', $craftsman);
            $this->assertObjectHasAttribute('email', $craftsman);
            $this->assertObjectHasAttribute('issueCount', $craftsman);
        }
    }

    /**
     * @param string $constructionSiteId
     * @return \stdClass
     */
    private function getConstructionSite(string $constructionSiteId)
    {
        $getUrl = '/api/edit/construction_site';

        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSiteId);

        $response = $this->authenticatedPostRequest($getUrl, $constructionSiteRequest);
        $constructionSiteData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $newConstructionSite = $constructionSiteData->data->constructionSite;
        return $newConstructionSite;
    }
}
