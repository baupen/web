<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Api\Entity\Foyer\Issue;
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
        $issuesData = $this->getIssues();

        $this->assertNotNull($issuesData->data);
        $this->assertNotNull($issuesData->data->issues);

        $this->assertTrue(is_array($issuesData->data->issues));
        foreach ($issuesData->data->issues as $issue) {
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

    /**
     * @return mixed
     */
    private function getIssues()
    {
        $url = '/api/foyer/issue/list';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        return $this->checkResponse($response, ApiStatus::SUCCESS);
    }

    public function testIssueUpdate()
    {
        $url = '/api/foyer/issue/update';

        $craftsman = $this->getSomeConstructionSite()->getCraftsmen()[0];
        $issues = [];
        foreach ($this->getIssues()->data->issues as $apiIssue) {
            $issue = new Issue($apiIssue->id);
            $issue->setIsMarked(false);
            $issue->setResponseLimit(new \DateTime());
            $issue->setCraftsmanId($craftsman->getId());
            $issue->setDescription("hello world");
            $issue->setWasAddedWithClient(true);
            $issues[] = $issue;
        }
        $request = new \App\Api\Request\Dispatch\IssuesRequest();
        $request->setIssues($issues);
        $request->setConstructionSiteId($this->getSomeConstructionSite()->getId());

        $response = $this->authenticatedPostRequest($url, $request);
        $issuesData = $this->checkResponse($response, ApiStatus::SUCCESS);

        dump($issuesData);
        $this->assertNotNull($issuesData->data);
        $this->assertNotNull($issuesData->data->issues);

        $this->assertTrue(is_array($issuesData->data->issues));
        $this->assertSameSize($issues, $issuesData->data->issues);
        foreach ($issuesData->data->issues as $issue) {
            $this->assertNotNull($issue);
            $this->assertEquals(false, $issue->isMarked);
            $this->assertEquals(true, $issue->wasAddedWithClient);
            $this->assertEquals("hello world", $issue->description);
            $this->assertEquals($craftsman->getId(), $issue->craftsmanId);
        }
    }
}