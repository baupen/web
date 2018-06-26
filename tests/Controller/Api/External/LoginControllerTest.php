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

use App\Api\External\Entity\Base\BaseEntity;
use App\Api\External\Entity\Building;
use App\Api\External\Entity\Craftsman;
use App\Api\External\Entity\Issue;
use App\Api\External\Entity\IssuePosition;
use App\Api\External\Entity\IssueStatus;
use App\Api\External\Entity\Map;
use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\ReadRequest;
use App\Controller\Api\External\Base\ExternalApiController;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\External\Base\ApiController;
use App\Tests\Controller\Base\FixturesTestCase;
use App\Tests\Controller\ServerData;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

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
        $this->checkResponse($response, ApiStatus::FAIL, ExternalApiController::UNKNOWN_USERNAME);

        $response = $doRequest('f@mangel.io', 'ad');
        $this->checkResponse($response, ApiStatus::FAIL, ExternalApiController::WRONG_PASSWORD);

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
