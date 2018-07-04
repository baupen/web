<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:11 PM
 */

namespace App\Tests\Controller\External\Api\Base;


use App\Entity\ConstructionSite;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\AbstractApiController;
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