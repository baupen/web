<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $errorRequest->setMessage('wops something happened');

        $response = $this->authenticatedPostRequest($url, $errorRequest);

        return $this->checkResponse($response, ApiStatus::SUCCESS);
    }
}
