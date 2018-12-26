<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        if ($this->client === null) {
            $this->client = static::createClient();
        }

        return $this->client;
    }

    /**
     * @param string $url
     *
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
        if ($this->someConstructionSite === null) {
            $this->someConstructionSite = $this->getClient()->getContainer()->get('doctrine')->getRepository(ConstructionSite::class)->findOneBy([]);
        }

        return $this->someConstructionSite;
    }
}
