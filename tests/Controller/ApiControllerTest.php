<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:57 AM
 */

namespace App\Tests\Controller;

use App\Api\Response\Base\AbstractResponse;
use App\Api\Response\ErrorResponse;
use App\Api\Response\FailResponse;
use App\Api\Response\SuccessfulResponse;
use App\Controller\ApiController;
use App\Enum\ApiStatus;
use App\Tests\Controller\Base\FixturesTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
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
        $serializer = $client->getContainer()->get("serializer");

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
        $this->checkResponse($serializer, $response,ApiStatus::FAIL, ApiController::UNKNOWN_USERNAME);

        $response = $doRequest("f@mangel.io", "ad");
        $this->checkResponse($serializer, $response,ApiStatus::FAIL, ApiController::WRONG_PASSWORD);

        $response = $doRequest("f@mangel.io", "asdf");
        $this->checkResponse($serializer, $response,ApiStatus::SUCCESSFUL);
    }

    private function checkResponse(SerializerInterface $serializer, Response $response, $apiStatus, $message = "")
    {
        if ($apiStatus == ApiStatus::SUCCESSFUL) {
            $successful = json_decode($response->getContent());
            $this->assertEquals($apiStatus, $successful->status, $response->getContent());
            $this->assertEquals(200, $response->getStatusCode());
        } else if ($apiStatus == ApiStatus::FAIL) {
            $failed = json_decode($response->getContent());
            $this->assertEquals($apiStatus, $failed->status);
            $this->assertEquals($message, $failed->message);
            $this->assertEquals(200, $response->getStatusCode());
        } else if ($apiStatus == ApiStatus::ERROR) {
            $error = json_decode($response->getContent());
            $this->assertEquals($apiStatus, $error->status);
            $this->assertEquals($message, $error->message);
            $this->assertNotEquals(200, $response->getStatusCode());
        }
    }
//    }
//
//    /**
//     * gets an authentication token
//     *
//     * @param Client $client
//     * @return string
//     */
//    private function getAuthenticationToken(Client $client)
//    {
//        $serializer = $client->getContainer()->get("serializer");
//
//        $client->request(
//            'POST',
//            '/api/login',
//            [],
//            [],
//            ["CONTENT_TYPE" => "application/json"],
//            '{"identifier":"j", "passwordHash":"' . hash("sha256", "asdf") . '"}'
//        );
//
//        $response = $client->getResponse();
//
//        $this->assertEquals(200, $response->getStatusCode());
//
//        /* @var LoginContent $loginResponse */
//        $loginResponse = $serializer->deserialize($response->getContent(), LoginContent::class, "json");
//        $this->assertEquals(ApiStatus::SUCCESSFUL, $loginResponse->getApiStatus());
//
//        return $loginResponse->getUser()["authenticationToken"];
//    }
//
//    /**
//     * tests the authentication works properly
//     */
//    public function testAuthenticationStatus()
//    {
//        $client = static::createClient();
//        $serializer = $client->getContainer()->get("serializer");
//
//        $doRequest = function ($authenticationToken) use ($client) {
//            $client->request(
//                'POST',
//                '/api/authentication_status',
//                [],
//                [],
//                ["CONTENT_TYPE" => "application/json"],
//                '{"authenticationToken":"' . $authenticationToken . '"}'
//            );
//        };
//
//        $checkResponse = function ($apiStatus) use ($client, $serializer) {
//            $response = $client->getResponse();
//
//            $this->assertEquals(200, $response->getStatusCode());
//
//
//            /* @var LoginContent $loginResponse */
//            $loginResponse = $serializer->deserialize($response->getContent(), LoginContent::class, "json");
//            $this->assertEquals($apiStatus, $loginResponse->getApiStatus());
//        };
//
//
//        $doRequest("unknwon");
//        $checkResponse(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
//
//        $realToken = $this->getAuthenticationToken($client);
//
//        $doRequest($realToken);
//        $checkResponse(ApiStatus::SUCCESSFUL);
//
//        $newToken = $this->getAuthenticationToken($client);
//
//        $doRequest($newToken);
//        $checkResponse(ApiStatus::SUCCESSFUL);
//
//        $doRequest($realToken);
//        $checkResponse(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
//    }
//
//    private function getSomeMarkerId(Client $client, $authenticationToken)
//    {
//        $serializer = $client->getContainer()->get("serializer");
//        $client->request(
//            'POST',
//            '/api/sync',
//            [],
//            [],
//            ["CONTENT_TYPE" => "application/json"],
//            '{"authenticationToken":"' . $authenticationToken . '"}'
//        );
//
//        $response = $client->getResponse();
//        $this->assertEquals(200, $response->getStatusCode());
//
//        /* @var SyncResponse $syncReponse */
//        $syncReponse = $serializer->deserialize($response->getContent(), SyncResponse::class, "json");
//        $this->assertEquals(ApiStatus::SUCCESSFUL, $syncReponse->getApiStatus());
//        return $syncReponse->getMarkers()[0]["id"];
//    }
//
//    /**
//     * tests the authentication works properly
//     */
//    public function testSyncPull()
//    {
//        $client = static::createClient();
//        $serializer = $client->getContainer()->get("serializer");
//
//        $realToken = $this->getAuthenticationToken($client);
//
//        $client->request(
//            'POST',
//            '/api/sync',
//            [],
//            [],
//            ["CONTENT_TYPE" => "application/json"],
//            '{"authenticationToken":"' . $realToken . '"}'
//        );
//
//        $response = $client->getResponse();
//        $this->assertEquals(200, $response->getStatusCode());
//
//        /* @var SyncResponse $syncReponse */
//        $syncReponse = $serializer->deserialize($response->getContent(), SyncResponse::class, "json");
//        $this->assertEquals(ApiStatus::SUCCESSFUL, $syncReponse->getApiStatus());
//
//        $this->assertTrue(count($syncReponse->getBuildings()) > 0);
//        $this->assertTrue(count($syncReponse->getCraftsmen()) > 0);
//        $this->assertTrue(count($syncReponse->getBuildingMaps()) > 0);
//        $this->assertTrue(count($syncReponse->getMarkers()) > 0);
//        $this->assertTrue($syncReponse->getUser() != null);
//    }
//
//    /**
//     * tests the authentication works properly
//     */
//    public function testSyncPush()
//    {
//        $client = static::createClient();
//        $serializer = $client->getContainer()->get("serializer");
//
//        $realToken = $this->getAuthenticationToken($client);
//
//        $client->request(
//            'POST',
//            '/api/sync',
//            [],
//            [],
//            ["CONTENT_TYPE" => "application/json"],
//            '{"authenticationToken":"' . $realToken . '"}'
//        );
//
//        $response = $client->getResponse();
//        $this->assertEquals(200, $response->getStatusCode());
//
//        /* @var SyncResponse $syncReponse */
//        $syncReponse = $serializer->deserialize($response->getContent(), SyncResponse::class, "json");
//        $this->assertEquals(ApiStatus::SUCCESSFUL, $syncReponse->getApiStatus());
//
//        $craftManGuid = $syncReponse->getCraftsmen()[0]["id"];
//        $buildingMapGuid = $syncReponse->getBuildingMaps()[0]["id"];
//
//        $client->request(
//            'POST',
//            '/api/sync',
//            [],
//            [],
//            ["CONTENT_TYPE" => "application/json"],
//            '{
//                "authenticationToken": "' . $realToken . '",
//                "markers": [
//                        {
//                            "markXPercentage": 0.621,
//                            "markYPercentage": 0.22,
//                            "frameXPercentage": 0.521,
//                            "frameYPercentage": 0.07,
//                            "content": "Consectetur et dolor sit.",
//                            "craftsman": "' . $craftManGuid . '",
//                            "buildingMap": "' . $buildingMapGuid . '",
//                            "imageFileName": "mark_image.jpg",
//                            "frameXHeight": 0.2,
//                            "frameYLength": 0.3,
//                            "approved": "2018-03-12T18:01:45.347956",
//                            "createdAt": "2018-03-12T18:01:45.347956",
//                            "lastChangedAt": "2018-03-12T18:01:45.347956",
//                            "fullIdentifier": "13.03.2018 08:14"
//                        }
//                ]
//            }'
//        );
//
//        $secondResponse = $client->getResponse();
//
//        $this->assertEquals(200, $secondResponse->getStatusCode());
//
//        /* @var SyncResponse $secondSyncReponse */
//        $secondSyncReponse = $serializer->deserialize($secondResponse->getContent(), SyncResponse::class, "json");
//        $this->assertEquals(ApiStatus::SUCCESSFUL, $secondSyncReponse->getApiStatus());
//
//        $this->assertTrue(count($secondSyncReponse->getMarkers()) - 1 == count($syncReponse->getMarkers()));
//    }
//
//    /**
//     * tests upload/download functionality
//     */
//    public function testFileUploadDownload()
//    {
//        $client = static::createClient();
//        $serializer = $client->getContainer()->get("serializer");
//
//        $realToken = $this->getAuthenticationToken($client);
//
//        $markerId = $this->getSomeMarkerId($client, $realToken);
//
//        $filePath = __DIR__ . "/../Files/sample.jpg";
//        $copyPath = __DIR__ . "/../Files/sample_2.jpg";
//        copy($filePath, $copyPath);
//
//        $file = new UploadedFile(
//            $copyPath,
//            'upload.jpg',
//            'image/jpeg'
//        );
//        $client->request(
//            'POST',
//            '/api/file/upload',
//            [],
//            [$markerId => $file],
//            ["HTTP_MANGEL_AUTHENTICATION_TOKEN" => $realToken]
//        );
//
//
//        $response = $client->getResponse();
//        $this->assertEquals(200, $response->getStatusCode());
//
//        /* @var SyncResponse $uploadResponse */
//        $uploadResponse = $serializer->deserialize($response->getContent(), AbstractResponse::class, "json");
//        $this->assertEquals(ApiStatus::SUCCESSFUL, $uploadResponse->getApiStatus());
//
//
//        $client->request(
//            'POST',
//            '/api/file/download',
//            [],
//            [],
//            ["CONTENT_TYPE" => "application/json"],
//            '{"authenticationToken":"' . $realToken . '", "fileName": "sample_2.jpg"}'
//        );
//
//        $response = $client->getResponse();
//        $this->assertEquals(200, $response->getStatusCode());
//    }
}