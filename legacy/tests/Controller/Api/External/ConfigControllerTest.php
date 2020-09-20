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

use App\Service\Interfaces\PathServiceInterface;
use App\Tests\Controller\Api\External\Base\ApiController;
use const DIRECTORY_SEPARATOR;
use function is_array;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConfigControllerTest extends ApiController
{
    /**
     * @var string
     */
    private $configFilePath;

    /**
     * ConfigControllerTest constructor.
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        self::bootKernel();
        $pathService = self::$container->get(PathServiceInterface::class);

        $this->configFilePath = $pathService->getTransientFolderRoot().DIRECTORY_SEPARATOR.'domainOverrides.json';
    }

    /**
     * tests the login functionality.
     */
    public function testDomainOverrides_configExists_returnsConfig()
    {
        $client = static::createClient();
        $this->createConfigFile();

        $response = $this->doConfigRequest($client);
        $trialResponse = json_decode($response->getContent());
        $this->assertNotNull($trialResponse->domainOverrides);

        $domainOverrides = $trialResponse->domainOverrides;
        $this->assertTrue(is_array($domainOverrides));
        $this->assertNotEmpty($domainOverrides);

        $entry = $domainOverrides[0];
        $this->assertNotNull($entry->userInputDomain);
        $this->assertNotNull($entry->serverURL);
        $this->assertNotNull($entry->userLoginDomain);

        $this->removeConfigFile();
    }

    /**
     * tests the login functionality.
     */
    public function testDomainOverrides_configDoesNotExist_returnsNotFound()
    {
        $client = static::createClient();
        $this->removeConfigFile();

        $this->expectException(NotFoundHttpException::class);
        $this->doConfigRequest($client);
    }

    private function createConfigFile()
    {
        file_put_contents($this->configFilePath, '{
  "domainOverrides": [
    {
      "userInputDomain":"mangel.io",
      "serverURL":"https://app.mangel.io",
      "userLoginDomain":"mangel.io"
    },
    {
      "userInputDomain":"dev.mangel.io",
      "serverURL":"https://dev.app.mangel.io",
      "userLoginDomain":"mangel.io"
    }
  ]
}');
    }

    private function removeConfigFile()
    {
        if (file_exists($this->configFilePath)) {
            unlink($this->configFilePath);
        }
    }

    /**
     * @return Response
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
