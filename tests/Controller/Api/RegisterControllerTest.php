<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api;

use App\Api\Entity\Register\UpdateIssue;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\Register\SetStatusRequest;
use App\Api\Request\Register\UpdateIssuesRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;
use function count;
use DateTime;
use function is_array;

class RegisterControllerTest extends ApiController
{
    public function testIssuesList()
    {
        $issuesData = $this->getIssues();

        $this->assertNotNull($issuesData->data);
        $this->assertNotNull($issuesData->data->issues);

        $this->assertTrue(is_array($issuesData->data->issues));
        $once = [false, false, false, false];
        $onceProperties = ['respondedAt', 'responseByName', 'reviewedAt', 'reviewByName'];
        foreach ($issuesData->data->issues as $issue) {
            $this->assertNotNull($issue);

            $this->assertObjectHasAttribute('isMarked', $issue);
            $this->assertObjectHasAttribute('wasAddedWithClient', $issue);
            $this->assertObjectHasAttribute('description', $issue);
            $this->assertObjectHasAttribute('imageThumbnail', $issue);
            $this->assertObjectHasAttribute('imageFull', $issue);
            $this->assertObjectHasAttribute('craftsmanId', $issue);
            $this->assertObjectHasAttribute('map', $issue);
            $this->assertObjectHasAttribute('uploadedAt', $issue);
            $this->assertObjectHasAttribute('uploadByName', $issue);
            $this->assertObjectHasAttribute('registrationByName', $issue);
            $this->assertObjectHasAttribute('registeredAt', $issue);

            for ($i = 0; $i < count($onceProperties); ++$i) {
                $once[$i] = $once[$i] || property_exists($issue, $onceProperties[$i]);
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
            $this->assertObjectHasAttribute('name', $craftsman);
            $this->assertObjectHasAttribute('trade', $craftsman);
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
            $this->assertObjectHasAttribute('name', $map);
            $this->assertObjectHasAttribute('children', $map);
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
            $updateIssue = new UpdateIssue($apiIssue->id);
            $updateIssue->setIsMarked(false);
            $updateIssue->setResponseLimit(new DateTime());
            $updateIssue->setCraftsmanId($craftsman->getId());
            $updateIssue->setDescription('hello world');
            $issues[] = $updateIssue;
        }
        $request = new UpdateIssuesRequest();
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
            $this->assertFalse($issue->isMarked);
            $this->assertSame('hello world', $issue->description);
            $this->assertSame($craftsman->getId(), $issue->craftsmanId);
        }
    }

    public function testIssueStatus()
    {
        $url = '/api/register/issue/status';

        $issuesIds = [];
        foreach ($this->getIssues()->data->issues as $apiIssue) {
            $issuesIds[] = $apiIssue->id;
        }
        $request = new SetStatusRequest();
        $request->setIssueIds($issuesIds);
        $request->setRespondedStatusSet(true);
        $request->setReviewedStatusSet(false);
        $request->setConstructionSiteId($this->getSomeConstructionSite()->getId());

        $response = $this->authenticatedPostRequest($url, $request);
        $issuesData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($issuesData->data);
        $this->assertNotNull($issuesData->data->issues);

        $this->assertTrue(is_array($issuesData->data->issues));
        $this->assertSameSize($issuesIds, $issuesData->data->issues);
        foreach ($issuesData->data->issues as $issue) {
            $this->assertNotNull($issue);
            $this->assertNotNull($issue->respondedAt);
            $this->assertNotNull($issue->responseByName);
            $this->assertNull($issue->reviewedAt);
            $this->assertNull($issue->reviewByName);
        }

        $request->setRespondedStatusSet(false);
        $request->setReviewedStatusSet(true);

        $response = $this->authenticatedPostRequest($url, $request);
        $issuesData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertTrue(is_array($issuesData->data->issues));
        $this->assertSameSize($issuesIds, $issuesData->data->issues);
        foreach ($issuesData->data->issues as $issue) {
            $this->assertNotNull($issue);
            $this->assertNull($issue->respondedAt);
            $this->assertNull($issue->responseByName);
            $this->assertNotNull($issue->reviewedAt);
            $this->assertNotNull($issue->reviewByName);
        }
    }
}
