<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:57 AM
 */

namespace App\Tests\Controller;

use App\Api\Response\LoginResponse;
use App\Api\Response\SyncResponse;
use App\Controller\ApiController;
use App\Enum\ApiStatus;
use App\Tests\Controller\Base\FixturesTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiControllerTest extends FixturesTestCase
{
    /**
     * Creates a Client.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server An array of server parameters
     *
     * @return Client A Client instance
     */
    protected static function createClient(array $options = array(), array $server = array())
    {
        $client = parent::createClient($options, $server);
        $client->getContainer()->set("serializer", ApiController::getSerializer());
        return $client;
    }

    /**
     * tests the login functionality
     */
    public function testLogin()
    {
        $client = static::createClient();
        $serializer = ApiController::getSerializer(); //$client->getContainer()->get("serializer");

        $doRequest = function ($identifier, $password) use ($client) {
            $client->request(
                'POST',
                '/api/login',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                '{"identifier":"' . $identifier . '", "passwordHash":"' . hash("sha256", $password) . '"}'
            );
        };

        $checkResponse = function ($apiStatus) use ($client, $serializer) {
            $response = $client->getResponse();

            $this->assertEquals(200, $response->getStatusCode());

            /* @var LoginResponse $loginResponse */
            $loginResponse = $serializer->deserialize($response->getContent(), LoginResponse::class, "json");
            $this->assertEquals($apiStatus, $loginResponse->getApiStatus());
        };


        $doRequest("unknwon", "ad");
        $checkResponse(ApiStatus::UNKNOWN_IDENTIFIER);

        $doRequest("j", "ad");
        $checkResponse(ApiStatus::WRONG_PASSWORD);

        $doRequest("j", "asdf");

        $checkResponse(ApiStatus::SUCCESSFUL);
    }

    /**
     * gets an authentication token
     *
     * @param Client $client
     * @return string
     */
    private function getAuthenticationToken(Client $client)
    {
        $serializer = $client->getContainer()->get("serializer");

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            '{"identifier":"j", "passwordHash":"' . hash("sha256", "asdf") . '"}'
        );

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        /* @var LoginResponse $loginResponse */
        $loginResponse = $serializer->deserialize($response->getContent(), LoginResponse::class, "json");
        $this->assertEquals(ApiStatus::SUCCESSFUL, $loginResponse->getApiStatus());

        return $loginResponse->getUser()["authenticationToken"];
    }

    /**
     * tests the authentication works properly
     */
    public function testAuthenticationStatus()
    {
        $client = static::createClient();
        $serializer = $client->getContainer()->get("serializer");

        $doRequest = function ($authenticationToken) use ($client) {
            $client->request(
                'POST',
                '/api/authentication_status',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                '{"authenticationToken":"' . $authenticationToken . '"}'
            );
        };

        $checkResponse = function ($apiStatus) use ($client, $serializer) {
            $response = $client->getResponse();

            $this->assertEquals(200, $response->getStatusCode());


            /* @var LoginResponse $loginResponse */
            $loginResponse = $serializer->deserialize($response->getContent(), LoginResponse::class, "json");
            $this->assertEquals($apiStatus, $loginResponse->getApiStatus());
        };


        $doRequest("unknwon");
        $checkResponse(ApiStatus::INVALID_AUTHENTICATION_TOKEN);

        $realToken = $this->getAuthenticationToken($client);

        $doRequest($realToken);
        $checkResponse(ApiStatus::SUCCESSFUL);

        $newToken = $this->getAuthenticationToken($client);

        $doRequest($newToken);
        $checkResponse(ApiStatus::SUCCESSFUL);

        $doRequest($realToken);
        $checkResponse(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
    }

    private function getSomeMarkerId(Client $client, $authenticationToken)
    {
        $serializer = $client->getContainer()->get("serializer");
        $client->request(
            'POST',
            '/api/sync',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            '{"authenticationToken":"' . $authenticationToken . '"}'
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        /* @var SyncResponse $syncReponse */
        $syncReponse = $serializer->deserialize($response->getContent(), SyncResponse::class, "json");
        $this->assertEquals(ApiStatus::SUCCESSFUL, $syncReponse->getApiStatus());
        return $syncReponse->getMarkers()[0]["id"];
    }

    /**
     * tests the authentication works properly
     */
    public function testSyncPull()
    {
        $client = static::createClient();
        $serializer = $client->getContainer()->get("serializer");

        $realToken = $this->getAuthenticationToken($client);

        $client->request(
            'POST',
            '/api/sync',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            '{"authenticationToken":"' . $realToken . '"}'
        );

        $response = $client->getResponse();
        file_put_contents("debug.html", $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());

        /* @var SyncResponse $syncReponse */
        $syncReponse = $serializer->deserialize($response->getContent(), SyncResponse::class, "json");
        $this->assertEquals(ApiStatus::SUCCESSFUL, $syncReponse->getApiStatus());

        $this->assertTrue(count($syncReponse->getBuildings()) > 0);
        $this->assertTrue(count($syncReponse->getCraftsmen()) > 0);
        $this->assertTrue(count($syncReponse->getBuildingMaps()) > 0);
        $this->assertTrue(count($syncReponse->getMarkers()) > 0);
        $this->assertTrue($syncReponse->getUser() != null);
    }

    /**
     * tests the authentication works properly
     */
    public function testSyncPush()
    {
        $client = static::createClient();
        $serializer = $client->getContainer()->get("serializer");

        $realToken = $this->getAuthenticationToken($client);

        $client->request(
            'POST',
            '/api/sync',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            '{"authenticationToken":"' . $realToken . '"}'
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        /* @var SyncResponse $syncReponse */
        $syncReponse = $serializer->deserialize($response->getContent(), SyncResponse::class, "json");
        $this->assertEquals(ApiStatus::SUCCESSFUL, $syncReponse->getApiStatus());

        $craftManGuid = $syncReponse->getCraftsmen()[0]["id"];
        $buildingMapGuid = $syncReponse->getBuildingMaps()[0]["id"];

        $client->request(
            'POST',
            '/api/sync',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            '{
                "authenticationToken": "' . $realToken . '",
                "markers": [
                        {
                            "markXPercentage": 0.621,
                            "markYPercentage": 0.22,
                            "frameXPercentage": 0.521,
                            "frameYPercentage": 0.07,
                            "content": "Consectetur et dolor sit.",
                            "craftsman": "' . $craftManGuid . '",
                            "buildingMap": "' . $buildingMapGuid . '",
                            "imageFileName": "mark_image.jpg",
                            "frameXHeight": 0.2,
                            "frameYLength": 0.3,
                            "approved": "2018-03-12T18:01:45.347956",
                            "createdAt": "2018-03-12T18:01:45.347956",
                            "lastChangedAt": "2018-03-12T18:01:45.347956",
                            "fullIdentifier": "13.03.2018 08:14"
                        }
                ]
            }'
        );

        $secondResponse = $client->getResponse();

        file_put_contents("example.html", $secondResponse->getContent());

        $this->assertEquals(200, $secondResponse->getStatusCode());

        /* @var SyncResponse $secondSyncReponse */
        $secondSyncReponse = $serializer->deserialize($secondResponse->getContent(), SyncResponse::class, "json");
        $this->assertEquals(ApiStatus::SUCCESSFUL, $secondSyncReponse->getApiStatus());

        $this->assertTrue(count($secondSyncReponse->getMarkers()) - 1 == count($syncReponse->getMarkers()));
    }

    /**
     * tests upload/download functionality
     */
    public function testFileUploadDownload()
    {
        $client = static::createClient();
        $serializer = $client->getContainer()->get("serializer");

        $realToken = $this->getAuthenticationToken($client);

        $markerId = $this->getSomeMarkerId($client, $realToken);

        $filePath = __DIR__ . "/../Files/sample.jpg";
        $copyPath = __DIR__ . "/../Files/sample_2.jpg";
        copy($filePath, $copyPath);

        $file = new UploadedFile(
            $copyPath,
            'upload.jpg',
            'image/jpeg'
        );
        $client->request(
            'POST',
            '/api/file/upload',
            [],
            [$markerId => $file],
            ["CONTENT_TYPE" => "application/json"],
            '{"authenticationToken":"' . $realToken . '"}'
        );


        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        /* @var SyncResponse $syncReponse */
        $syncReponse = $serializer->deserialize($response->getContent(), SyncResponse::class, "json");
        $this->assertEquals(ApiStatus::SUCCESSFUL, $syncReponse->getApiStatus());


        $client->request(
            'POST',
            '/api/file/download',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            '{"authenticationToken":"' . $realToken . '", "fileName": "sample_2.jpg"}'
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
}