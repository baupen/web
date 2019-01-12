<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\External;

use App\Api\External\Request\TrialRequest;
use App\Api\External\Response\Data\TrialData;
use App\Api\External\Transformer\TrialUserTransformer;
use App\Controller\Api\External\Base\ExternalApiController;
use App\Service\Interfaces\TrialServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/trial")
 *
 * @return Response
 */
class TrialController extends ExternalApiController
{
    /**
     * @Route("", name="api_external_trial", methods={"GET"})
     *
     * @param Request $request
     * @param TrialUserTransformer $trialUserTransformer
     * @param TrialServiceInterface $trialService
     *
     * @return Response
     */
    public function trialAction(Request $request, TrialUserTransformer $trialUserTransformer, TrialServiceInterface $trialService)
    {
        /** @var TrialRequest $trialRequest */
        if (!$this->parseRequest($request, TrialRequest::class, $trialRequest, $errorResponse)) {
            return $errorResponse;
        }

        $manager = $trialService->createTrialAccount($trialRequest->getProposedGivenName(), $trialRequest->getProposedFamilyName());

        //construct answer
        $user = $trialUserTransformer->toApi($manager);
        $loginData = new TrialData($user);

        return $this->success($loginData);
    }
}
