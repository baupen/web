<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:57 AM
 */

namespace App\Tests\Controller;

use App\Api\Entity\Base\BaseEntity;
use App\Api\Entity\Building;
use App\Api\Entity\Craftsman;
use App\Api\Entity\Issue;
use App\Api\Entity\IssuePosition;
use App\Api\Entity\IssueStatus;
use App\Api\Entity\Map;
use App\Api\Entity\ObjectMeta;
use App\Api\Entity\User;
use App\Api\Request\ReadRequest;
use App\Api\Response\Base\AbstractResponse;
use App\Api\Response\ErrorResponse;
use App\Api\Response\FailResponse;
use App\Api\Response\SuccessfulResponse;
use App\Controller\ApiController;
use App\Enum\ApiStatus;
use App\Tests\Controller\Base\FixturesTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;

class ApiControllerTest extends FixturesTestCase
{
    /**
     * tests the login functionality
     */
    public function testLogin()
    {
        $client = static::createClient();
        $doRequest = function ($username, $password) use ($client) {
            $client->request(
                'POST',
                '/api/login',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                '{"username":"' . $username . '", "passwordHash":"' . hash("sha256", $password) . '"}'
            );

            return $client->getResponse();
        };


        $response = $doRequest("unknwon", "ad");
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::UNKNOWN_USERNAME);

        $response = $doRequest("f@mangel.io", "ad");
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::WRONG_PASSWORD);

        $response = $doRequest("f@mangel.io", "asdf");
        $loginResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($loginResponse->data);
        $this->assertNotNull($loginResponse->data->user);
        $this->assertNotNull($loginResponse->data->user->givenName);
        $this->assertNotNull($loginResponse->data->user->familyName);
        $this->assertNotNull($loginResponse->data->user->authenticationToken);
        $this->assertNotNull($loginResponse->data->user->meta->id);
        $this->assertNotNull($loginResponse->data->user->meta->lastChangeTime);
    }

    /**
     * @param Response $response
     * @param $apiStatus
     * @param string $message
     * @return mixed|null
     */
    private function checkResponse(Response $response, $apiStatus, $message = "")
    {
        $this->assertFalse(strpos($response->getContent(),"\u00") > 0);
        if ($apiStatus == ApiStatus::SUCCESS) {
            $successful = json_decode($response->getContent());
            $this->assertEquals($apiStatus, $successful->status, $response->getContent());
            $this->assertEquals(200, $response->getStatusCode());
            return $successful;
        } else if ($apiStatus == ApiStatus::FAIL) {
            $failed = json_decode($response->getContent());
            $this->assertEquals($apiStatus, $failed->status, $response->getContent());
            $this->assertEquals($message, $failed->message);
            $this->assertEquals(400, $response->getStatusCode());
            return $failed;
        } else if ($apiStatus == ApiStatus::ERROR) {
            $error = json_decode($response->getContent());
            $this->assertEquals($apiStatus, $error->status);
            $this->assertEquals($message, $error->message);
            $this->assertNotEquals(500, $response->getStatusCode());
            return $error;
        }
        return null;
    }

    /**
     * gets an authenticated user
     *
     * @param Client $client
     * @return \stdClass
     */
    private function getAuthenticatedUser(Client $client)
    {
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            '{"username":"f@mangel.io", "passwordHash":"' . hash("sha256", "asdf") . '"}'
        );

        $json = $client->getResponse()->getContent();
        $response = json_decode($json);
        return $response->data->user;
    }

    /**
     * get the state of the server
     *
     * @param Client $client
     * @param $authenticatedUser
     * @return ServerData
     */
    private function getServerEntities(Client $client, $authenticatedUser)
    {
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function (ReadRequest $readRequest) use ($client, $serializer) {
            $json = $serializer->serialize($readRequest, "json");
            $client->request(
                'POST',
                '/api/read',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        # update all
        $readRequest = new ReadRequest();
        $readRequest->setAuthenticationToken($authenticatedUser->authenticationToken);

        $userMeta = new ObjectMeta();
        $userMeta->setId($authenticatedUser->meta->id);
        $userMeta->setLastChangeTime((new \DateTime())->setTimestamp(0)->format("c"));
        $readRequest->setUser($userMeta);

        $readRequest->setBuildings([]);
        $readRequest->setCraftsmen([]);
        $readRequest->setIssues([]);
        $readRequest->setMaps([]);

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($readResponse->data);
        $this->assertNotNull($readResponse->data->changedUser);
        $this->assertNotNull($readResponse->data->changedBuildings);
        $this->assertTrue(count($readResponse->data->changedBuildings) > 0);
        $this->assertTrue(count($readResponse->data->changedCraftsmen) > 0);
        $this->assertTrue(count($readResponse->data->changedMaps) > 0);
        $this->assertTrue(count($readResponse->data->changedIssues) > 0);

        $buildings = [];
        foreach ($readResponse->data->changedBuildings as $stdClass) {
            $buildings[] = $serializer->deserialize(json_encode($stdClass), Building::class, "json");
        }
        $craftsmen = [];
        foreach ($readResponse->data->changedCraftsmen as $stdClass) {
            $craftsmen[] = $serializer->deserialize(json_encode($stdClass), Craftsman::class, "json");
        }
        $maps = [];
        foreach ($readResponse->data->changedMaps as $stdClass) {
            $maps[] = $serializer->deserialize(json_encode($stdClass), Map::class, "json");
        }
        $issues = [];
        foreach ($readResponse->data->changedIssues as $stdClass) {
            $issues[] = $serializer->deserialize(json_encode($stdClass), Issue::class, "json");
        }

        $this->assertNotNull($readResponse->data->changedBuildings[0]->address);

        return new ServerData($buildings, $maps, $craftsmen, $issues);
    }

    /**
     * tests the create issue method
     */
    public function testRead()
    {
        $client = static::createClient();
        $authenticatedUser = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function (ReadRequest $readRequest) use ($client, $serializer) {
            $json = $serializer->serialize($readRequest, "json");
            $client->request(
                'POST',
                '/api/read',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };
        $serverData = $this->getServerEntities($client, $authenticatedUser);

        ### update none
        $readRequest = new ReadRequest();
        $readRequest->setAuthenticationToken($authenticatedUser->authenticationToken);
        $userMeta = new ObjectMeta();
        $userMeta->setId($authenticatedUser->meta->id);
        $userMeta->setLastChangeTime($authenticatedUser->meta->lastChangeTime);
        $readRequest->setUser($userMeta);

        //transform objects to meta object
        $getMetas = function (array $entities, $invalids = 1, $old = 0, $lost = 0) {
            //convert to object meta
            $metas = [];
            foreach ($entities as $entity) {
                /** @var BaseEntity $entity */
                if ($lost-- > 0) {
                    //skip to lose meta
                    continue;
                }
                $meta = new ObjectMeta();
                $meta->setId($entity->getMeta()->getId());
                if ($old-- > 0) {
                    //set to min datetime to force update
                    $meta->setLastChangeTime(((new \DateTime())->setTimestamp(0)->format("c")));
                } else {
                    $meta->setLastChangeTime($entity->getMeta()->getLastChangeTime());
                }
                $metas[] = $meta;
            }

            for ($i = 0; $i < $invalids; $i++) {
                //create invalid & add
                $meta = new ObjectMeta();
                $meta->setId(Uuid::uuid4());
                $meta->setLastChangeTime((new \DateTime())->setTimestamp(0)->format("c"));
                $metas[] = $meta;
            }

            return $metas;
        };

        //set them in the request
        $readRequest->setBuildings($getMetas($serverData->getBuildings()));
        $readRequest->setCraftsmen($getMetas($serverData->getCraftsmen()));
        $readRequest->setMaps($getMetas($serverData->getMaps()));
        $readRequest->setIssues($getMetas($serverData->getIssues()));

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($readResponse->data);
        $this->assertNull($readResponse->data->changedUser);
        $this->assertEmpty($readResponse->data->changedBuildings);
        $this->assertEmpty($readResponse->data->changedCraftsmen);
        $this->assertEmpty($readResponse->data->changedMaps);
        $this->assertEmpty($readResponse->data->changedIssues);
        $this->assertCount(1, $readResponse->data->removedBuildingIDs);
        $this->assertCount(1, $readResponse->data->removedCraftsmanIDs);
        $this->assertCount(1, $readResponse->data->removedMapIDs);
        $this->assertCount(1, $readResponse->data->removedIssueIDs);

        ### update, remove & add at the same time
        //set them in the request
        $readRequest->setBuildings($getMetas($serverData->getBuildings(), 1, 1, 1));
        $readRequest->setCraftsmen($getMetas($serverData->getCraftsmen(), 1, 1, 1));
        $readRequest->setMaps($getMetas($serverData->getMaps(), 1, 1, 1));
        $readRequest->setIssues($getMetas($serverData->getIssues(), 1, 1, 1));

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($readResponse->data);
        $this->assertNull($readResponse->data->changedUser);
        $this->assertCount(2, $readResponse->data->changedBuildings);
        $this->assertCount(2, $readResponse->data->changedCraftsmen);
        $this->assertCount(2, $readResponse->data->changedMaps);
        $this->assertCount(2, $readResponse->data->changedIssues);
        $this->assertCount(1, $readResponse->data->removedBuildingIDs);
        $this->assertCount(1, $readResponse->data->removedCraftsmanIDs);
        $this->assertCount(1, $readResponse->data->removedMapIDs);
        $this->assertCount(1, $readResponse->data->removedIssueIDs);
    }

    /**
     * tests the create issue method
     */
    public function testCreateIssue()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function (Issue $issue) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, "json") . '}';
            $client->request(
                'POST',
                '/api/issue/create',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);

        $issue = new Issue();
        $issue->setWasAddedWithClient(true);
        $issue->setIsMarked(true);
        $issue->setDescription("description");
        $issue->setMap($serverData->getMaps()[0]->getMeta()->getId());

        $issue->setStatus(new IssueStatus());

        $meta = new ObjectMeta();
        $meta->setId($this->getNewGuid());
        $meta->setLastChangeTime((new \DateTime())->format("c"));
        $issue->setMeta($meta);

        $issuePosition = new IssuePosition();
        $issuePosition->setX(0.4);
        $issuePosition->setY(0.3);
        $issuePosition->setZoomScale(0.5);
        $issue->setPosition($issuePosition);

        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response has issue
        $this->assertNotNull($issueResponse->data);
        $this->assertNotNull($issueResponse->data->issue);
        $checkIssue = $issueResponse->data->issue;
        //fully check issue
        $this->verifyIssue($checkIssue, $issue);

        $response = $doRequest($issue);
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::ISSUE_GUID_ALREADY_IN_USE);

        //check issue without position
        $issue->setPosition(null);
        $issue->getMeta()->setId($this->getNewGuid());
        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);
        $this->verifyIssue($issueResponse->data->issue, $issue);
    }

    /**
     * checks if the issue is of the expected form
     *
     * @param $checkIssue
     * @param Issue $issue
     */
    private function verifyIssue($checkIssue, Issue $issue)
    {
        //check properties
        $this->assertEquals($checkIssue->wasAddedWithClient, $issue->getWasAddedWithClient());
        $this->assertEquals($checkIssue->isMarked, $issue->getIsMarked());
        $this->assertEquals($checkIssue->imageFilename, $issue->getImageFilename());
        $this->assertEquals($checkIssue->description, $issue->getDescription());
        $this->assertEquals($checkIssue->map, $issue->getMap());

        //check meta is newer/equal & id is preserved
        $this->assertEquals($checkIssue->meta->id, $issue->getMeta()->getId());
        $this->assertTrue($checkIssue->meta->lastChangeTime >= $issue->getMeta()->getLastChangeTime());

        //check position transferred correctly
        if ($issue->getPosition() != null) {
            $this->assertNotNull($checkIssue->position);
            $this->assertEquals($checkIssue->position->x, $issue->getPosition()->getX());
            $this->assertEquals($checkIssue->position->y, $issue->getPosition()->getY());
            $this->assertEquals($checkIssue->position->zoomScale, $issue->getPosition()->getZoomScale());
        } else {
            $this->assertNull($checkIssue->position);
        }

        //check status
        $this->assertNotNull($checkIssue->status);
    }

    /**
     * tests the create issue method
     */
    public function testUpdateIssue()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function (Issue $issue) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, "json") . '}';
            $client->request(
                'POST',
                '/api/issue/update',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);

        $imageFilename = $this->getNewGuid() . ".jpg";

        /** @var Issue $issue */
        $issue = $serverData->getIssues()[0];
        $issue->setWasAddedWithClient(false);
        $issue->setIsMarked(false);
        $issue->setDescription("description 2");
        $issue->setMap($serverData->getMaps()[0]->getMeta()->getId());

        $issue->setStatus(new IssueStatus());

        $issuePosition = new IssuePosition();
        $issuePosition->setX(0.4);
        $issuePosition->setY(0.3);
        $issuePosition->setZoomScale(0.5);
        $issue->setPosition($issuePosition);

        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response has issue
        $this->assertNotNull($issueResponse->data);
        $this->assertNotNull($issueResponse->data->issue);
        $checkIssue = $issueResponse->data->issue;
        //fully check issue
        $this->verifyIssue($checkIssue, $issue);

        //check with non-existing
        $issue->getMeta()->setId($this->getNewGuid());
        $response = $doRequest($issue);
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::ISSUE_NOT_FOUND);
    }

    private function getNewGuid()
    {
        return strtoupper(Uuid::uuid4()->toString());
    }


    /**
     * tests upload/download functionality
     */
    public function testFileUploadDownload()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function ($issue, UploadedFile $file) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, "json") . '}';
            $client->request(
                'POST',
                '/api/issue/update',
                [],
                ["some key" => $file],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);

        /** @var Issue[] $newIssues */
        /** @var Issue[] $registeredIssues */
        /** @var Issue[] $respondedIssues */
        /** @var Issue[] $reviewedIssues */
        $this->categorizeIssues($serverData->getIssues(), $newIssues, $registeredIssues, $respondedIssues, $reviewedIssues);
        $issue = $newIssues[0];

        $filePath = __DIR__ . "/../Files/sample.jpg";
        $copyPath = __DIR__ . "/../Files/sample_2.jpg";
        copy($filePath, $copyPath);

        $file = new UploadedFile(
            $copyPath,
            'upload.jpg',
            'image/jpeg'
        );
        $issue->setImageFilename(Uuid::uuid4()->toString() . ".jpg");
        $response = $doRequest($issue, $file);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response issue updated
        $this->verifyIssue($issueResponse->data->issue, $issue);
        //refresh issue version
        $issue = $serializer->deserialize(json_encode($issueResponse->data->issue), Issue::class, "json");

        $client = static::createClient();
        $doRequest = function (ObjectMeta $objectMeta) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($objectMeta, "json") . '}';
            $client->request(
                'POST',
                '/api/file/download',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $response = $doRequest($issue->getMeta());
        $this->assertInstanceOf(BinaryFileResponse::class, $response);

        //test building image download
        $client = static::createClient();
        $doRequest = function (ObjectMeta $objectMeta) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "building":' . $serializer->serialize($objectMeta, "json") . '}';
            $client->request(
                'POST',
                '/api/file/download',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $response = $doRequest($serverData->getBuildings()[0]->getMeta());
        $this->assertInstanceOf(BinaryFileResponse::class, $response);

        //test map download
        $client = static::createClient();
        $doRequest = function (ObjectMeta $objectMeta) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "map":' . $serializer->serialize($objectMeta, "json") . '}';
            $client->request(
                'POST',
                '/api/file/download',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $response = $doRequest($serverData->getMaps()[0]->getMeta());
        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    /**
     * tests upload/download functionality
     */
    public function testIssueActions()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function ($issueId, $action) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issueID":"' . $issueId . '"}';
            $client->request(
                'POST',
                '/api/issue/' . $action,
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);
        $issue = $serverData->getIssues()[0];

        $response = $doRequest($issue->getMeta()->getId(), "mark");
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response issue updated
        $issue->setIsMarked(!$issue->getIsMarked());
        $this->verifyIssue($issueResponse->data->issue, $issue);
        $issue = $serializer->deserialize(json_encode($issueResponse->data->issue), Issue::class, "json");

        /** @var Issue[] $newIssues */
        /** @var Issue[] $registeredIssues */
        /** @var Issue[] $respondedIssues */
        /** @var Issue[] $reviewedIssues */
        $this->categorizeIssues($serverData->getIssues(), $newIssues, $registeredIssues, $respondedIssues, $reviewedIssues);

        //delete
        $response = $doRequest($newIssues[0]->getMeta()->getId(), "delete");
        $this->checkResponse($response, ApiStatus::SUCCESS);
        $response = $doRequest($newIssues[0]->getMeta()->getId(), "delete");
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::ISSUE_NOT_FOUND);

        //review registered
        $response = $doRequest($registeredIssues[0]->getMeta()->getId(), "review");
        $this->checkResponse($response, ApiStatus::SUCCESS);
        $response = $doRequest($registeredIssues[0]->getMeta()->getId(), "review");
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::ISSUE_ACTION_NOT_ALLOWED);

        //review responded
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), "review");
        $this->checkResponse($response, ApiStatus::SUCCESS);
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), "review");
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::ISSUE_ACTION_NOT_ALLOWED);

        //revert reviewed
        $response = $doRequest($reviewedIssues[0]->getMeta()->getId(), "revert");
        $this->checkResponse($response, ApiStatus::SUCCESS);

        //revert responded
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), "revert");
        $this->checkResponse($response, ApiStatus::SUCCESS);
        //revert twice because of earlier actions
        $doRequest($respondedIssues[0]->getMeta()->getId(), "revert");
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), "revert");
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::ISSUE_ACTION_NOT_ALLOWED);
    }

    /**
     * @param Issue[] $issues
     * @param Issue[] $newIssues
     * @param Issue[] $registeredIssues
     * @param Issue[] $respondedIssues
     * @param Issue[] $reviewedIssues
     */
    private function categorizeIssues($issues, &$newIssues, &$registeredIssues, &$respondedIssues, &$reviewedIssues)
    {
        $newIssues = [];
        $registeredIssues = [];
        $respondedIssues = [];
        $reviewedIssues = [];

        foreach ($issues as $issue) {
            if ($issue->getStatus()->getReview() != null) {
                $reviewedIssues[] = $issue;
            } else if ($issue->getStatus()->getResponse() != null) {
                $respondedIssues[] = $issue;
            } else if ($issue->getStatus()->getRegistration() != null) {
                $registeredIssues[] = $issue;
            } else {
                $newIssues[] = $issue;
            }
        }
    }
}