<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:11 PM
 */

namespace App\Tests\Controller\External\Api\Base;


use App\Entity\ConstructionSite;
use App\Tests\Controller\Api\Base\AbstractApiController;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends AbstractApiController
{
    /**
     * @var Client
     */
    private $client = null;

    /**
     * @return Client
     */
    protected function getClient()
    {
        if ($this->client == null) {
            $this->client = static::createClient();
        }
        return $this->client;
    }

    /**
     * @param string $url
     * @return Response
     */
    protected function authenticatedGetRequest($url)
    {
        $client = $this->getClient();

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
            $this->someConstructionSite = $this->getClient()->getContainer()->get('doctrine')->getRepository(ConstructionSite::class)->findOneBy([]);
        }
        return $this->someConstructionSite;
    }
}