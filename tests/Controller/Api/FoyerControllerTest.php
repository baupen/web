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

class FoyerControllerTest extends ApiController
{
    public function testIssuesList()
    {
        $url = '/api/foyer/issue/list';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $craftsmanData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($craftsmanData->data);
        $this->assertNotNull($craftsmanData->data->issues);

        $this->assertTrue(is_array($craftsmanData->data->issues));
        foreach ($craftsmanData->data->issues as $issue) {
            $this->assertNotNull($issue);
            $this->assertObjectHasAttribute("isMarked", $issue);
            $this->assertObjectHasAttribute("wasAddedWithClient", $issue);
            $this->assertObjectHasAttribute("description", $issue);
            $this->assertObjectHasAttribute("imageFilePath", $issue);
            $this->assertObjectHasAttribute("responseLimit", $issue);
            $this->assertObjectHasAttribute("craftsmanId", $issue);
            $this->assertObjectHasAttribute("map", $issue);
            $this->assertObjectHasAttribute("uploadedAt", $issue);
            $this->assertObjectHasAttribute("uploadByName", $issue);
        }
    }

    public function testCraftsmanList()
    {
        $url = '/api/foyer/craftsman/list';

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
        }
    }
}