<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:57 AM
 */

namespace App\Tests\Controller;

use App\Api\Response\Normalizers\LoginResponseNormalizer;
use App\Api\Response\LoginResponse;
use App\Enum\ApiStatus;
use App\Tests\Controller\Base\FixturesTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiControllerTest extends FixturesTestCase
{
    /**
     * tests the login functionality
     */
    public function testLogin()
    {
        $client = static::createClient();
        $serializer = $client->getContainer()->get("serializer");

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
     * tests the login functionality
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
}