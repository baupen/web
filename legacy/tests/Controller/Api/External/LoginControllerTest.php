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

use App\Controller\Api\External\LoginController;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\External\Base\ApiController;

class LoginControllerTest extends ApiController
{
    /**
     * tests the login functionality.
     */
    public function testLogin()
    {
        $client = static::createClient();
        $doRequest = function ($username, $password) use ($client) {
            $client->request(
                'POST',
                '/api/external/login',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                '{"username":"' . $username . '", "passwordHash":"' . hash('sha256', $password) . '"}'
            );

            return $client->getResponse();
        };

        $response = $doRequest('unknwon', 'ad');
        $this->checkResponse($response, ApiStatus::FAIL, LoginController::UNKNOWN_USERNAME);

        $response = $doRequest('f@mangel.io', 'ad');
        $this->checkResponse($response, ApiStatus::FAIL, LoginController::WRONG_PASSWORD);

        $response = $doRequest('f@mangel.io', 'asdf');
        $loginResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($loginResponse->data);
        $this->assertNotNull($loginResponse->data->user);
        $this->assertNotNull($loginResponse->data->user->givenName);
        $this->assertNotNull($loginResponse->data->user->familyName);
        $this->assertNotNull($loginResponse->data->user->authenticationToken);
        $this->assertNotNull($loginResponse->data->user->meta->id);
        $this->assertNotNull($loginResponse->data->user->meta->lastChangeTime);
    }
}
