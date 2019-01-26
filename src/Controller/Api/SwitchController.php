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
use App\Api\Response\Data\ConstructionSitesData;
use App\Api\Response\Data\EmptyData;
use App\Api\Transformer\Switch_\ConstructionSiteTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/switch")
 */
class SwitchController extends ApiController
{
    const INCORRECT_NUMBER_OF_FILES = 'incorrect number of files';
    const MAP_FILE_UPLOAD_FAILED = 'map file could not be uploaded';

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        return parent::errorMessageToStatusCode($message);
    }

    /**
     * @Route("/construction_sites", name="api_switch_constrution_sites")
     *
     * @param ConstructionSiteTransformer $constructionSiteTransformer
     *
     * @return Response
     */
    public function constructionSitesAction(ConstructionSiteTransformer $constructionSiteTransformer)
    {
        $constructionSites = $this->getDoctrine()->getRepository(ConstructionSite::class)->findAll();

        //create response
        $data = new ConstructionSitesData();
        $data->setConstructionSites($constructionSiteTransformer->toApiMultiple($constructionSites));

        return $this->success($data);
    }

    /**
     * @Route("/request_access", name="api_switch_request_access")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function requestAccessAction(Request $request)
    {
        /** @var ConstructionSiteRequest $parsedRequest */
        if (!parent::parseRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse)) {
            return $errorResponse;
        }

        // add to construction site if not already a member
        /** @var ConstructionSite $constructionSite */
        $constructionSite = $this->getDoctrine()->getRepository(ConstructionSite::class)->find($parsedRequest->getConstructionSiteId());
        if (!$constructionSite->getConstructionManagers()->contains($this->getUser())) {
            $constructionSite->getConstructionManagers()->add($this->getUser());
            $this->fastSave($constructionSite);
        }

        return $this->success(new EmptyData());
    }

    /**
     * @Route("/remove_access", name="api_switch_remove_access")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function removeAccessAction(Request $request)
    {
        /** @var ConstructionSiteRequest $parsedRequest */
        if (!parent::parseRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse)) {
            return $errorResponse;
        }

        // add to construction site if not already a member
        /** @var ConstructionSite $constructionSite */
        $constructionSite = $this->getDoctrine()->getRepository(ConstructionSite::class)->find($parsedRequest->getConstructionSiteId());
        if ($constructionSite->getConstructionManagers()->contains($this->getUser())) {
            $constructionSite->getConstructionManagers()->removeElement($this->getUser());
            $this->fastSave($constructionSite);
        }

        return $this->success(new EmptyData());
    }
}
