<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Api\Entity\Foyer\Issue;
use App\Api\Entity\Register\UpdateIssue;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\IssueRequest;
use App\Api\Request\IssuesRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RegisterControllerTest extends ApiController
{
    /**
     * @throws \ReflectionException
     */
    public function testIssuesList()
    {
        $issuesData = $this->getIssues();

        $this->assertNotNull($issuesData->data);
        $this->assertNotNull($issuesData->data->issues);

        $this->assertTrue(is_array($issuesData->data->issues));
        $once = [false, false, false, false];
        $onceProperties = ["respondedAt", "responseByName", "reviewedAt", "reviewByName"];
        foreach ($issuesData->data->issues as $issue) {
            $this->assertNotNull($issue);

            $this->assertObjectHasAttribute("isMarked", $issue);
            $this->assertObjectHasAttribute("wasAddedWithClient", $issue);
            $this->assertObjectHasAttribute("description", $issue);
            $this->assertObjectHasAttribute("imageThumbnail", $issue);
            $this->assertObjectHasAttribute("imageFull", $issue);
            $this->assertObjectHasAttribute("craftsmanId", $issue);
            $this->assertObjectHasAttribute("map", $issue);
            $this->assertObjectHasAttribute("uploadedAt", $issue);
            $this->assertObjectHasAttribute("uploadByName", $issue);
            $this->assertObjectHasAttribute("registrationByName", $issue);
            $this->assertObjectHasAttribute("registeredAt", $issue);

            for ($i = 0; $i < count($onceProperties); $i++) {
                $once[$i] =  $once[$i] || property_exists($issue, $onceProperties[$i]);
            }
        }

        foreach ($once as $item) {
            $this->assertTrue($item);
        }
    }

    public function testCraftsmanList()
    {
        $url = '/api/register/craftsman/list';

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

    public function testMapList()
    {
        $url = '/api/register/map/list';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapData->data);
        $this->assertNotNull($mapData->data->maps);

        $this->assertTrue(is_array($mapData->data->maps));
        foreach ($mapData->data->maps as $map) {
            $this->assertNotNull($map);
            $this->assertObjectHasAttribute("name", $map);
            $this->assertObjectHasAttribute("context", $map);
            $this->assertObjectHasAttribute("children", $map);
        }
    }

    /**
     * @return mixed
     */
    private function getIssues()
    {
        $url = '/api/register/issue/list';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        return $this->checkResponse($response, ApiStatus::SUCCESS);
    }


    public function testIssueUpdate()
    {
        $url = '/api/register/issue/update';

        $craftsman = $this->getSomeConstructionSite()->getCraftsmen()[0];
        $issues = [];
        foreach ($this->getIssues()->data->issues as $apiIssue) {
            $issue = new UpdateIssue($apiIssue->id);
            $issue->setIsMarked(false);
            $issue->setResponseLimit(new \DateTime());
            $issue->setCraftsmanId($craftsman->getId());
            $issue->setDescription("hello world");
            $issues[] = $issue;
        }
        $request = new \App\Api\Request\Foyer\UpdateIssuesRequest();
        $request->setUpdateIssues($issues);
        $request->setConstructionSiteId($this->getSomeConstructionSite()->getId());

        $response = $this->authenticatedPostRequest($url, $request);
        $issuesData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($issuesData->data);
        $this->assertNotNull($issuesData->data->issues);

        $this->assertTrue(is_array($issuesData->data->issues));
        $this->assertSameSize($issues, $issuesData->data->issues);
        foreach ($issuesData->data->issues as $issue) {
            $this->assertNotNull($issue);
            $this->assertEquals(false, $issue->isMarked);
            $this->assertEquals("hello world", $issue->description);
            $this->assertEquals($craftsman->getId(), $issue->craftsmanId);
        }
    }
}