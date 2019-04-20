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

use App\Api\Entity\Note\UpdateNote;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\Note\NoteIdRequest;
use App\Api\Request\Note\UpdateNoteRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;
use function count;

class NoteControllerTest extends ApiController
{
    public function testNoteList()
    {
        $mapData = $this->getNoteList();
        $this->assertNotNull($mapData->data);
        $this->assertNotNull($mapData->data->notes);
        $this->assertNotEmpty($mapData->data->notes);
        foreach ($mapData->data->notes as $note) {
            $this->assertObjectHasAttribute('id', $note);
            $this->assertObjectHasAttribute('content', $note);
            $this->assertObjectHasAttribute('timestamp', $note);
            $this->assertObjectHasAttribute('authorName', $note);
        }
    }

    public function testNoteCreate()
    {
        $url = '/api/note/create';

        $dataBefore = $this->getNoteList();

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new UpdateNoteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $updateNode = new UpdateNote();
        $updateNode->setContent('my note');
        $constructionSiteRequest->setNote($updateNode);
        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $noteData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($noteData->data);
        $this->assertNotNull($noteData->data->note);

        $dataAfter = $this->getNoteList();
        $this->assertTrue(count($dataBefore->data->notes) + 1 === count($dataAfter->data->notes));
    }

    public function testNoteUpdate()
    {
        $url = '/api/note/update';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new UpdateNoteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $updateNode = new UpdateNote();
        $updateNode->setContent('my note');
        $updateNode->setId($this->getNote()->id);
        $constructionSiteRequest->setNote($updateNode);

        $dataBefore = $this->getNoteList();

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $noteData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($noteData->data);
        $this->assertNotNull($noteData->data->note);

        $dataAfter = $this->getNoteList();
        $this->assertTrue(count($dataBefore->data->notes) === count($dataAfter->data->notes));
    }

    public function testNoteDelete()
    {
        $url = '/api/note/delete';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new NoteIdRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());
        $constructionSiteRequest->setNoteId($this->getNote()->id);

        $dataBefore = $this->getNoteList();

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $this->checkResponse($response, ApiStatus::SUCCESS);

        $dataAfter = $this->getNoteList();
        $this->assertTrue(count($dataBefore->data->notes) - 1 === count($dataAfter->data->notes));
    }

    private function getNoteList()
    {
        $url = '/api/note/list';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);

        return $this->checkResponse($response, ApiStatus::SUCCESS);
    }

    private function getNote()
    {
        $url = '/api/note/create';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new UpdateNoteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $updateNode = new UpdateNote();
        $updateNode->setContent('my note');
        $constructionSiteRequest->setNote($updateNode);
        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $noteData = $this->checkResponse($response, ApiStatus::SUCCESS);

        return $noteData->data->note;
    }
}
