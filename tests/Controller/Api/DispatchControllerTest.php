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
use App\Tests\Controller\Api\Base\ApiController;
use App\Tests\Controller\Base\FixturesTestCase;
use App\Tests\Mock\MockEmailService;

class DispatchControllerTest extends ApiController
{
    public function testCraftsmanList()
    {
        $url = '/api/dispatch/craftsman/list';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
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

    public function testDispatch()
    {
        $url = '/api/dispatch';

        $client = $this->getAuthenticatedClient();
        $client->getContainer()->set(EmailServiceInterface::class, new MockEmailService());

        $constructionSite = $this->getSomeConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];
        $dispatchRequest = new CraftsmenRequest();
        $dispatchRequest->setConstructionSiteId($constructionSite->getId());
        $dispatchRequest->setCraftsmanIds([$craftsman->getId()]);

        $response = $this->authenticatedPostRequest($url, $dispatchRequest);
        $craftsmanData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($craftsmanData->data);
        $this->assertObjectHasAttribute("successfulIds", $craftsmanData->data);
        $this->assertObjectHasAttribute("skippedIds", $craftsmanData->data);
        $this->assertObjectHasAttribute("failedIds", $craftsmanData->data);

        /** @var MockEmailService $emailService */
        $emailService = $client->getContainer()->get(EmailServiceInterface::class);
        $this->assertTrue(count($emailService->getReceivers()) > 0);
    }
}