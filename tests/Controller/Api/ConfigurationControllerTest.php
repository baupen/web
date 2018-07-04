<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;

class ConfigurationControllerTest extends ApiController
{
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