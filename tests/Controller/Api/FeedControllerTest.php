<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:40 PM
 */

namespace App\Tests\Controller\Api;


use App\Api\Entity\Foyer\Issue;
use App\Api\Entity\Note\UpdateNote;
use App\Api\Entity\Register\UpdateIssue;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\IssueIdRequest;
use App\Api\Request\IssueIdsRequest;
use App\Api\Request\Note\NoteIdRequest;
use App\Api\Request\Note\UpdateNoteRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FeedControllerTest extends ApiController
{
    public function testFeedList()
    {
        $url = '/api/feed/list';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $data = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($data->data);
        $this->assertNotNull($data->data->feed);
        $this->assertNotEmpty($data->data->feed->entries);
        foreach ($data->data->feed->entries as $entry) {
            $this->assertObjectHasAttribute("id", $entry);
            $this->assertObjectHasAttribute("craftsmanName", $entry);
            $this->assertObjectHasAttribute("timestamp", $entry);
            $this->assertObjectHasAttribute("type", $entry);
        }
    }
}