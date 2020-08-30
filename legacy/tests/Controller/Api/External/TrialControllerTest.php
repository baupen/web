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

use App\Enum\ApiStatus;
use App\Tests\Controller\Api\External\Base\ApiController;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class TrialControllerTest extends ApiController
{
    /**
     * tests the login functionality.
     */
    public function testTrial()
    {
        $client = static::createClient();

        $response = $this->doTrialRequest($client, null, null);
        $trialResponse = $this->checkResponse($response, ApiStatus::SUCCESS);
        $this->assertNotNull($trialResponse->data);
        $this->assertNotNull($trialResponse->data->trialUser);
        $this->assertNotNull($trialResponse->data->trialUser->username);
        $this->assertNotNull($trialResponse->data->trialUser->password);

        $username = $trialResponse->data->trialUser->username;
        $this->assertTrue(mb_strlen($username) >= 20 && mb_strlen($username) < 25);
        // ensure username is of the form some_prefix@test.personalurl.ch
        $this->assertStringEndsWith('@test.mangel.io', $username);

        $password = $trialResponse->data->trialUser->password;
        $this->assertTrue(mb_strlen($password) > 8);

        $response = $this->doLoginRequest($client, $username, $password);
        $loginResponse = $this->checkResponse($response, ApiStatus::SUCCESS);
        $this->assertNotNull($loginResponse->data->user->givenName);
        $this->assertNotNull($loginResponse->data->user->familyName);
    }

    /**
     * tests the login functionality.
     */
    public function testRecommendationAccepted()
    {
        $givenName = 'Anna';
        $familyName = 'Schweigert';

        $client = static::createClient();

        $response = $this->doTrialRequest($client, $givenName, $familyName);
        $trialResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $username = $trialResponse->data->trialUser->username;
        $password = $trialResponse->data->trialUser->password;

        $response = $this->doLoginRequest($client, $username, $password);
        $loginResponse = $this->checkResponse($response, ApiStatus::SUCCESS);
        $this->assertSame($givenName, $loginResponse->data->user->givenName);
        $this->assertNotNull($familyName, $loginResponse->data->user->familyName);
    }

    /**
     * @param Client $client
     * @param $username
     * @param $password
     *
     * @return Response
     */
    private function doLoginRequest(Client $client, $username, $password)
    {
        $client->request(
            'POST',
            '/api/external/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"username":"' . $username . '", "passwordHash":"' . hash('sha256', $password) . '"}'
        );

        return $client->getResponse();
    }

    /**
     * @param Client $client
     * @param string|null $givenName
     * @param string|null $familyName
     *
     * @return Response
     */
    private function doTrialRequest(Client $client, ?string $givenName, ?string $familyName)
    {
        $givenNamePayload = $givenName === null ? 'null' : '"' . $givenName . '"';
        $familyNamePayload = $familyName === null ? 'null' : '"' . $familyName . '"';
        $client->request(
            'POST',
            '/api/external/trial/create_account',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"proposedGivenName":' . $givenNamePayload . ', "proposedFamilyName":' . $familyNamePayload . '}'
        );

        return $client->getResponse();
    }
}
