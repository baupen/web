<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:11 PM
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
        if ($this->authenticatedClient == null) {
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
     * @return Response
     */
    protected function authenticatedPostRequest($url, $payload, $files = [])
    {
        $client = $this->getAuthenticatedClient();

        $client->request(
            'POST',
            $url,
            [],
            $files,
            ['CONTENT_TYPE' => 'application/json'],
            $client->getContainer()->get('serializer')->serialize($payload, 'json')
        );

        return $client->getResponse();
    }

    /**
     * @param string $url
     * @return Response
     */
    protected function authenticatedGetRequest($url)
    {
        $client = $this->getAuthenticatedClient();

        $client->request(
            'GET',
            $url
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
        if ($this->someConstructionSite == null) {
            $this->someConstructionSite = $this->getAuthenticatedClient()->getContainer()->get('doctrine')->getRepository(ConstructionSite::class)->findOneBy([]);
        }
        return $this->someConstructionSite;
    }
}