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
use App\Api\Response\Data\CraftsmanResponse;
use App\Controller\Api\Base\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/craftsman")
 */
class CraftsmanController extends AbstractApiController
{
    /**
     * @Route("/list", name="api_craftsman_list", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        if (!$this->parseRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse)) {
            return $errorResponse;
        }

        $this->ensureAccessAllowed($constructionSite);

        $data = new CraftsmanResponse();
        $data->setCraftsmen($constructionSite->getCraftsmen());

        return $this->json($this->get('serializer')->serialize($craftsmen, 'json', ['attributes' => ['name']]));
    }

    /**
     * gives the appropiate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        // TODO: Implement errorMessageToStatusCode() method.
    }
}
