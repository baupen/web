<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:11 PM
 */

namespace App\Tests\Controller\Api\Base;


use App\Entity\ConstructionSite;
use App\Enum\ApiStatus;
use App\Tests\Controller\Base\FixturesTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Api\External\Entity\Base\BaseEntity;
use App\Api\External\Entity\Building;
use App\Api\External\Entity\Craftsman;
use App\Api\External\Entity\Issue;
use App\Api\External\Entity\IssuePosition;
use App\Api\External\Entity\IssueStatus;
use App\Api\External\Entity\Map;
use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\ReadRequest;
use App\Tests\Controller\ServerData;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Client;

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
     * @return Response
     */
    protected function authenticatedPostRequest($url, $payload)
    {
        $client = $this->getAuthenticatedClient();

        $client->request(
            'POST',
            $url,
            [],
            [],
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
     * @return ConstructionSite
     */
    protected function getSomeConstructionSite()
    {
        return $this->getAuthenticatedClient()->getContainer()->get('doctrine')->getRepository(ConstructionSite::class)->findOneBy([]);
    }
}