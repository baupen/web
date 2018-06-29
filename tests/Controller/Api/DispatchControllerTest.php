<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\CraftsmenRequest;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Enum\ApiStatus;
use App\Service\Interfaces\EmailServiceInterface;
use App\Tests\Controller\Api\Base\AbstractApiController;
use App\Tests\Controller\Base\FixturesTestCase;
use App\Tests\Mock\MockEmailService;

class DispatchControllerTest extends AbstractApiController
{
    /**
     * tests the login functionality.
     */
    public function testCraftsmanList()
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
                '/api/dispatch/craftsman/list',
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
            $this->assertObjectHasAttribute("notReadIssuesCount", $craftsman);
            $this->assertObjectHasAttribute("notRespondedIssuesCount", $craftsman);
            $this->assertObjectHasAttribute("nextResponseLimit", $craftsman);
            $this->assertObjectHasAttribute("lastEmailSent", $craftsman);
            $this->assertObjectHasAttribute("lastOnlineVisit", $craftsman);
        }
    }

    /**
     * tests the login functionality.
     */
    public function testDispatch()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'f@mangel.io',
            'PHP_AUTH_PW' => 'asdf',
        ]);
        $client->getContainer()->set(EmailServiceInterface::class, new MockEmailService());
        $serializer = $client->getContainer()->get('serializer');
        /** @var ConstructionSite $constructionSite */
        $constructionSite = $client->getContainer()->get('doctrine')->getRepository(ConstructionSite::class)->findOneBy([]);
        /** @var Craftsman $craftsman */
        $craftsman = $client->getContainer()->get('doctrine')->getRepository(Craftsman::class)->findOneBy(["constructionSite" => $constructionSite]);
        $doRequest = function ($request) use ($client, $serializer) {
            $client->request(
                'POST',
                '/api/dispatch',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $serializer->serialize($request, 'json')
            );

            return $client->getResponse();
        };

        $dispatchRequest = new CraftsmenRequest();
        $dispatchRequest->setConstructionSiteId($constructionSite->getId());
        $dispatchRequest->setCraftsmanIds([$craftsman->getId()]);

        $response = $doRequest($dispatchRequest);
        $craftsmanData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($craftsmanData->data);
        $this->assertObjectHasAttribute("successful", $craftsmanData->data);
        $this->assertObjectHasAttribute("skipped", $craftsmanData->data);
        $this->assertObjectHasAttribute("failed", $craftsmanData->data);

        /** @var MockEmailService $emailService */
        $emailService = $client->getContainer()->get(EmailServiceInterface::class);
        $this->assertTrue(count($emailService->getReceivers()) > 0);
    }
}