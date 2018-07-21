<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Api\Entity\Foyer\Issue;
use App\Api\Entity\Foyer\UpdateIssue;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\IssueRequest;
use App\Api\Request\IssuesRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
            $this->assertObjectHasAttribute("imageThumbnail", $issue);
            $this->assertObjectHasAttribute("imageFull", $issue);
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

    public function testIssueImage()
    {
        $url = '/api/foyer/issue/image';

        $apiIssue = $this->getIssues()->data->issues[0];

        $request = new IssueRequest();
        $request->setIssueId($apiIssue->id);
        $request->setConstructionSiteId($this->getSomeConstructionSite()->getId());

        $filePath = __DIR__ . '/../../Files/sample.jpg';
        $copyPath = __DIR__ . '/../../Files/sample_2.jpg';
        copy($filePath, $copyPath);
        $file = new UploadedFile(
            $copyPath,
            'upload.jpg',
            'image/jpeg'
        );

        $response = $this->authenticatedPostRequest($url, $request, ["some_key" => $file]);
        $issuesData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($issuesData->data);
        $this->assertNotNull($issuesData->data->issue);

        $this->assertEquals($issuesData->data->issue->id, $apiIssue->id);
        $this->assertNotEquals($issuesData->data->issue->imageThumbnail, $apiIssue->imageThumbnail);
        $this->assertNotEquals($issuesData->data->issue->imageFull, $apiIssue->imageFull);
    }

    public function testIssueDelete()
    {
        $url = '/api/foyer/issue/delete';

        $ids = [];
        foreach ($this->getIssues()->data->issues as $issue) {
            $ids[] = $issue->id;
        }

        $request = new IssuesRequest();
        $request->setIssueIds($ids);
        $request->setConstructionSiteId($this->getSomeConstructionSite()->getId());

        $response = $this->authenticatedPostRequest($url, $request);
        $issuesData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($issuesData->data);
        $this->assertNotNull($issuesData->data->deletedIssues);

        $this->assertSameSize($ids, $issuesData->data->deletedIssues);

        $issues = $this->getIssues();
        $this->assertTrue(count($issues->data->issues) === 0);
    }

    public function testIssueConfirm()
    {
        $url = '/api/foyer/issue/confirm';

        $ids = [];
        foreach ($this->getIssues()->data->issues as $issue) {
            $ids[] = $issue->id;
        }

        $request = new IssuesRequest();
        $request->setIssueIds($ids);
        $request->setConstructionSiteId($this->getSomeConstructionSite()->getId());

        $response = $this->authenticatedPostRequest($url, $request);
        $issuesData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($issuesData->data);
        $this->assertNotNull($issuesData->data->numberIssues);

        $this->assertSameSize($ids, $issuesData->data->numberIssues);

        $seenNumbers = [];
        foreach ($issuesData->data->numberIssues as $numberIssue) {
            $this->assertNotNull($numberIssue);

            $this->assertObjectHasAttribute("number", $numberIssue);
            $this->assertObjectHasAttribute("id", $numberIssue);

            $this->assertNotContains($numberIssue->number, $seenNumbers);
            $seenNumbers[] = $numberIssue->number;
        }

        $issues = $this->getIssues();
        $this->assertTrue(count($issues->data->issues) === 0);
    }
}