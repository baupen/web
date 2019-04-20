<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api\External;

use App\Api\External\Entity\File;
use App\Api\External\Entity\Issue;
use App\Api\External\Entity\ObjectMeta;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\External\Base\ApiController;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileControllerTest extends ApiController
{
    /**
     * tests upload/download functionality.
     *
     * @throws Exception
     */
    public function testFileUploadDownload()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get('serializer');
        $doRequest = function ($issue, UploadedFile $file) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, 'json') . '}';
            $client->request(
                'POST',
                '/api/external/issue/update',
                [],
                ['some key' => $file],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);

        /* @var Issue[] $newIssues */
        /* @var Issue[] $registeredIssues */
        /* @var Issue[] $respondedIssues */
        /* @var Issue[] $reviewedIssues */
        $this->categorizeIssues($serverData->getIssues(), $newIssues, $registeredIssues, $respondedIssues, $reviewedIssues);
        $issue = $newIssues[0];

        $filePath = __DIR__ . '/../../../Files/sample.jpg';
        $copyPath = __DIR__ . '/../../../Files/sample_2.jpg';
        copy($filePath, $copyPath);

        $file = new UploadedFile(
            $copyPath,
            'upload.jpg',
            'image/jpeg'
        );
        $issueImage = new File();
        $issueImage->setFilename(Uuid::uuid4()->toString() . '.jpg');
        $issueImage->setId(Uuid::uuid4()->toString());
        $issue->setImage($issueImage);
        $response = $doRequest($issue, $file);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response issue updated
        $this->verifyIssue($issueResponse->data->issue, $issue);
        //refresh issue version
        $issue = $serializer->deserialize(json_encode($issueResponse->data->issue), Issue::class, 'json');

        $client = static::createClient();
        $doRequest = function (ObjectMeta $objectMeta) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($objectMeta, 'json') . '}';
            $client->request(
                'POST',
                '/api/external/file/download',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $response = $doRequest($issue->getMeta());
        $this->assertInstanceOf(BinaryFileResponse::class, $response, $response->getContent());

        //test building image download
        $client = static::createClient();
        $doRequest = function (ObjectMeta $objectMeta) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "constructionSite":' . $serializer->serialize($objectMeta, 'json') . '}';
            $client->request(
                'POST',
                '/api/external/file/download',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $imageBuilding = null;
        foreach ($serverData->getConstructionSites() as $building) {
            if ($building->getImage() !== null) {
                $imageBuilding = $building;
                break;
            }
        }
        if ($imageBuilding !== null) {
            $response = $doRequest($imageBuilding->getMeta());
            $this->assertInstanceOf(BinaryFileResponse::class, $response, $response->getContent());
        }

        //test map download
        $client = static::createClient();
        $doRequest = function (ObjectMeta $objectMeta) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "map":' . $serializer->serialize($objectMeta, 'json') . '}';
            $client->request(
                'POST',
                '/api/external/file/download',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $imageMap = null;
        foreach ($serverData->getMaps() as $map) {
            if ($map->getFile() !== null) {
                $imageMap = $map;
                break;
            }
        }
        if ($imageMap !== null) {
            $response = $doRequest($imageMap->getMeta());
            $this->assertInstanceOf(BinaryFileResponse::class, $response, $response->getContent());
        }
    }
}
