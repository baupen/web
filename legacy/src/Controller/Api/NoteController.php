<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\Note\NoteIdRequest;
use App\Api\Request\Note\UpdateNoteRequest;
use App\Api\Response\Data\EmptyData;
use App\Api\Response\Data\NoteData;
use App\Api\Response\Data\NotesData;
use App\Api\Transformer\Base\NoteTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Note;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/note")
 */
class NoteController extends ApiController
{
    const ISSUE_NOT_FOUND = 'not found';
    const ISSUE_EDIT_NOT_ALLOWED = 'not allowed to edit issue';

    /**
     * @Route("/list", name="api_note_list", methods={"POST"})
     *
     * @return Response
     */
    public function listAction(Request $request, NoteTransformer $noteTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $notes = $this->getDoctrine()->getRepository(Note::class)->findBy(['constructionSite' => $constructionSite->getId()], ['createdAt' => 'DESC']);
        /** @var \App\Api\Entity\Base\Note[] $apiNotes */
        $apiNotes = [];
        $canEdit = function ($issue) {
            return $this->canEditNote($issue);
        };

        foreach ($notes as $note) {
            $apiNotes[] = $noteTransformer->toApi($note, $canEdit);
        }

        //return data
        $overviewData = new NotesData();
        $overviewData->setNotes($apiNotes);

        return $this->success($overviewData);
    }

    /**
     * @Route("/create", name="api_note_create", methods={"POST"})
     *
     * @return Response
     */
    public function createAction(Request $request, NoteTransformer $noteTransformer)
    {
        return $this->executeUpdateNoteRequest($request, $noteTransformer);
    }

    /**
     * @Route("/update", name="api_note_update", methods={"POST"})
     *
     * @return Response
     */
    public function updateAction(Request $request, NoteTransformer $noteTransformer)
    {
        return $this->executeUpdateNoteRequest($request, $noteTransformer);
    }

    /**
     * @Route("/delete", name="api_note_delete", methods={"POST"})
     *
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var NoteIdRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, NoteIdRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //get note object
        $entity = $this->getDoctrine()->getRepository(Note::class)->findOneBy(['id' => $parsedRequest->getNoteId(), 'constructionSite' => $constructionSite->getId()]);
        if ($entity === null) {
            return $this->fail(self::ISSUE_NOT_FOUND);
        }
        if ($entity->getCreatedBy() !== $this->getUser()) {
            return $this->fail(self::ISSUE_EDIT_NOT_ALLOWED);
        }

        //remove & send success
        $this->fastRemove($entity);

        return $this->success(new EmptyData());
    }

    private function canEditNote(Note $note)
    {
        return $note->getCreatedBy() === $this->getUser();
    }

    /**
     * create or update a note entity.
     *
     * @return JsonResponse
     */
    private function executeUpdateNoteRequest(Request $request, NoteTransformer $noteTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateNoteRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateNoteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        if ($parsedRequest->getNote()->getId() !== null) {
            $entity = $this->getDoctrine()->getRepository(Note::class)->findOneBy(['id' => $parsedRequest->getNote()->getId(), 'constructionSite' => $constructionSite->getId()]);
            if ($entity === null) {
                return $this->fail(self::ISSUE_NOT_FOUND);
            }
            if ($entity->getCreatedBy() !== $this->getUser()) {
                return $this->fail(self::ISSUE_EDIT_NOT_ALLOWED);
            }
        } else {
            $entity = new Note();
            $entity->setCreatedBy($this->getUser());
            $entity->setConstructionSite($constructionSite);
        }

        $entity->setContent($parsedRequest->getNote()->getContent());
        $this->fastSave($entity);

        $noteData = new NoteData();
        $noteData->setNote($noteTransformer->toApi($entity, function ($issue) {
            return $this->canEditNote($issue);
        }));

        return $this->success($noteData);
    }
}
