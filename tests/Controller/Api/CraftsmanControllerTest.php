<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Entity\ConstructionSite;
use App\Tests\Controller\Base\FixturesTestCase;

class CraftsmanControllerTest extends FixturesTestCase
{
    /**
     * tests the login functionality.
     */
    public function testList()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'f@mangel.io',
            'PHP_AUTH_PW' => 'asdf',
        ]);
        /** @var ConstructionSite $constructionSite */
        $constructionSite = $client->getContainer()->get('doctrine')->getRepository(ConstructionSite::class)->findOneBy([]);
        $doRequest = function ($constructionSiteId) use ($client) {
            $client->request(
                'GET',
                '/api/craftsman/' . $constructionSiteId . '/list'
            );

            return $client->getResponse();
        };

        $response = $doRequest($constructionSite->getId());

        $this->assertNotNull($response->getContent());
    }
}