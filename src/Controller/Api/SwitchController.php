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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/switch")
 */
class SwitchController extends ApiController
{
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
     * throws an exception if a trial account is used to authenticate.
     */
    private function ensureNoTrialAccount()
    {
        if ($this->getUser()->getIsTrialAccount()) {
            throw new AccessDeniedHttpException();
        }
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
        $this->ensureNoTrialAccount();

        $constructionSites = $this->getDoctrine()->getRepository(ConstructionSite::class)->findBy(['isTrialConstructionSite' => false]);

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
        $this->ensureNoTrialAccount();

        /** @var ConstructionSiteRequest $parsedRequest */
        if (!parent::parseRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse)) {
            return $errorResponse;
        }

        // add to construction site if not already a member
        /** @var ConstructionSite $constructionSite */
        $constructionSites = $this->getDoctrine()->getRepository(ConstructionSite::class)->findBy(['isTrialConstructionSite' => false, 'id' => $parsedRequest->getConstructionSiteId()]);
        if (\count($constructionSites) === 0) {
            return $this->fail(self::CONSTRUCTION_SITE_NOT_FOUND);
        }

        $constructionSite = $constructionSites[0];
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
        $this->ensureNoTrialAccount();

        /** @var ConstructionSiteRequest $parsedRequest */
        if (!parent::parseRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse)) {
            return $errorResponse;
        }

        // add to construction site if not already a member
        /** @var ConstructionSite $constructionSite */
        $constructionSites = $this->getDoctrine()->getRepository(ConstructionSite::class)->findBy(['isTrialConstructionSite' => false, 'id' => $parsedRequest->getConstructionSiteId()]);
        if (\count($constructionSites) === 0) {
            return $this->fail(self::CONSTRUCTION_SITE_NOT_FOUND);
        }

        $constructionSite = $constructionSites[0];
        if ($constructionSite->getConstructionManagers()->contains($this->getUser())) {
            $constructionSite->getConstructionManagers()->removeElement($this->getUser());
            $this->fastSave($constructionSite);
        }

        return $this->success(new EmptyData());
    }
}
