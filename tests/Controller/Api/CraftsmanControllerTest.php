<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Api\Request\ConstructionSiteRequest;
use App\Entity\ConstructionSite;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\AbstractApiController;
use App\Tests\Controller\Base\FixturesTestCase;

class CraftsmanControllerTest extends AbstractApiController
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
        $serializer = $client->getContainer()->get('serializer');

        /** @var ConstructionSite $constructionSite */
        $constructionSite = $client->getContainer()->get('doctrine')->getRepository(ConstructionSite::class)->findOneBy([]);
        $doRequest = function ($constructionSite) use ($client, $serializer) {
            $client->request(
                'POST',
                '/api/craftsman/list',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $serializer->serialize($constructionSite, 'json')
            );

            return $client->getResponse();
        };

        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $doRequest($constructionSiteRequest);
        $craftsmanData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($craftsmanData->data);
        $this->assertNotNull($craftsmanData->data->craftsmen);

        $this->assertTrue(is_array($craftsmanData->data->craftsmen));
        foreach ($craftsmanData->data->craftsmen as $craftsman) {
            $this->assertNotNull($craftsman);
            $this->assertObjectHasAttribute("name", $craftsman);
            $this->assertObjectHasAttribute("trade", $craftsman);
            $this->assertObjectHasAttribute("unreadIssuesCount", $craftsman);
            $this->assertObjectHasAttribute("openIssuesCount", $craftsman);
            $this->assertObjectHasAttribute("nextResponseLimit", $craftsman);
            $this->assertObjectHasAttribute("lastEmailSent", $craftsman);
            $this->assertObjectHasAttribute("lastOnlineVisit", $craftsman);
        }
    }
}