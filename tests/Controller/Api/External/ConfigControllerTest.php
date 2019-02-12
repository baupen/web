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
use App\Service\Interfaces\PathServiceInterface;
use App\Tests\Controller\Api\External\Base\ApiController;
use Symfony\Bundle\FrameworkBundle\Client;

class ConfigControllerTest extends ApiController
{
    /**
     * tests the login functionality.
     */
    public function testDomainOverrides_configExists_returnsConfig()
    {
        $client = static::createClient();
        $this->createConfigFile($client);

        $response = $this->doConfigRequest($client);
        $trialResponse = $this->checkResponse($response, ApiStatus::SUCCESS);
        $this->assertNotNull($trialResponse->data);
        $this->assertNotNull($trialResponse->data->domainOverrides);

        $domainOverrides = $trialResponse->data->domainOverrides;
        $this->assertIsArray($domainOverrides);
        $this->assertNotEmpty($domainOverrides);

        $entry = $domainOverrides[0];
        $this->assertNotNull($entry->userInputDomain);
        $this->assertNotNull($entry->serverUrl);
        $this->assertNotNull($entry->userLoginDomain);

        $this->removeConfigFile($client);
    }

    /**
     * tests the login functionality.
     */
    public function testDomainOverrides_configDoesNotExist_returnsNotFound()
    {
        $client = static::createClient();
        $this->removeConfigFile($client);

        $response = $this->doConfigRequest($client);
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @param Client $client
     */
    private function createConfigFile(Client $client)
    {
        file_put_contents($this->getConfigFilePath($client), '{
  "domainOverrides": [
    {
      "userInputDomain":"mangel.io",
      "serverUrl":"https://app.mangel.io",
      "userLoginDomain":"mangel.io"
    },
    {
      "userInputDomain":"dev.mangel.io",
      "serverUrl":"https://dev.app.mangel.io",
      "userLoginDomain":"mangel.io"
    }
  ]
}');
    }

    /**
     * @param Client $client
     */
    private function removeConfigFile(Client $client)
    {
        $path = $this->getConfigFilePath($client);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * @param Client $client
     *
     * @return string
     */
    private function getConfigFilePath(Client $client)
    {
        /** @var PathServiceInterface $pathService */
        $pathService = $client->getContainer()->get(PathServiceInterface::class);

        return $pathService->getTransientFolderRoot() . \DIRECTORY_SEPARATOR . 'domainOverrides.json';
    }

    /**
     * @param Client $client
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function doConfigRequest(Client $client)
    {
        $client->request(
            'GET',
            '/api/external/config/domain_overrides'
        );

        return $client->getResponse();
    }
}
