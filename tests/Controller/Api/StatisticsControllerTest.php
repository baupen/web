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
use App\Api\Request\IssueIdRequest;
use App\Api\Request\IssueIdsRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StatisticsControllerTest extends ApiController
{
    public function testMapList()
    {
        $url = '/api/statistics/issues/overview';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $mapData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($mapData->data);
        $this->assertNotNull($mapData->data->overview);
        $overview = $mapData->data->overview;

        $this->assertObjectHasAttribute("newIssuesCount", $overview);
        $this->assertObjectHasAttribute("openIssuesCount", $overview);
        $this->assertObjectHasAttribute("markedIssuesCount", $overview);
        $this->assertObjectHasAttribute("overdueIssuesCount", $overview);
        $this->assertObjectHasAttribute("respondedNotReviewedIssuesCount", $overview);
    }
}