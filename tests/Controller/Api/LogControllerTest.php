<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Api\Request\Log\ErrorRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;

class LogControllerTest extends ApiController
{
    public function testConfiguration()
    {
        $url = '/api/log/error';

        $errorRequest = new ErrorRequest();
        $errorRequest->setMessage("wops something happened");

        $response = $this->authenticatedPostRequest($url, $errorRequest);
        return $this->checkResponse($response, ApiStatus::SUCCESS);
    }
}