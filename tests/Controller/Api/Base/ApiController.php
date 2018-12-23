<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api\Base;

use App\Entity\ConstructionSite;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends AbstractApiController
{
    /**
     * @var Client
     */
    private $authenticatedClient = null;

    /**
     * @return Client
     */
    protected function getAuthenticatedClient()
    {
        if ($this->authenticatedClient === null) {
            $this->authenticatedClient = static::createClient([], [
                'PHP_AUTH_USER' => 'f@mangel.io',
                'PHP_AUTH_PW' => 'asdf',
            ]);
        }

        return $this->authenticatedClient;
    }

    /**
     * @param string $url
     * @param mixed $payload
     * @param array $files
     *
     * @return Response
     */
    protected function authenticatedPostRequest($url, $payload, $files = [])
    {
        return $this->authenticatedRequest($url, 'POST', false, $payload, $files);
    }

    /**
     * @param string $url
     * @param mixed $payload
     * @param array $files
     *
     * @return Response
     */
    protected function authenticatedPutRequest($url, $payload, $files = [])
    {
        return $this->authenticatedRequest($url, 'PUT', false, $payload, $files);
    }

    /**
     * @param string $url
     *
     * @return Response
     */
    protected function authenticatedGetRequest($url)
    {
        return $this->authenticatedRequest($url, 'GET', false);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array|bool $headers
     * @param null $payload
     * @param array $files
     *
     * @return Response
     */
    private function authenticatedRequest(string $url, string $method, $headers, $payload = null, array $files = [])
    {
        $client = $this->getAuthenticatedClient();

        $headerArray = $headers === false ? [] : ['CONTENT_TYPE' => 'application/json'];
        $payloadValue = $payload === null ? null : $client->getContainer()->get('serializer')->serialize($payload, 'json');

        $client->request(
            $method,
            $url,
            [],
            $files,
            $headerArray,
            $payloadValue
        );

        return $client->getResponse();
    }

    /**
     * @var ConstructionSite
     */
    private $someConstructionSite = null;

    /**
     * @return ConstructionSite
     */
    protected function getSomeConstructionSite()
    {
        if ($this->someConstructionSite === null) {
            $this->someConstructionSite = $this->getAuthenticatedClient()->getContainer()->get('doctrine')->getRepository(ConstructionSite::class)->findOneBy([]);
        }

        return $this->someConstructionSite;
    }
}
