<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:57 AM
 */

namespace App\Tests\Controller;

use App\Api\Response\LoginResponse;
use App\Enum\ApiStatus;
use App\Tests\Controller\Base\FixturesTestCase;

class ApiControllerTest extends FixturesTestCase
{
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
                '{"identifier":"' . $identifier . '", "password_hash":"' . hash("sha256", $password) . '"}'
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
}