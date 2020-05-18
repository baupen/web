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

use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;

class ConfigurationControllerTest extends ApiController
{
    public static function setUpBeforeClass()
    {
        self::reset();
    }

    public function testConfiguration()
    {
        $url = '/api/configuration';

        $response = $this->authenticatedGetRequest($url);
        $configurationData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($configurationData->data);
        $this->assertNotNull($configurationData->data->constructionSite);
        $this->assertNotNull($configurationData->data->constructionSite->id);
    }
}
