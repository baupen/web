<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\External\Base;

use App\Api\External\Request\Base\AuthenticatedRequest;
use App\Controller\Api\Base\AbstractApiController;
use App\Entity\AuthenticationToken;
use App\Entity\ConstructionManager;
use Symfony\Component\HttpFoundation\Request;

class ExternalApiController extends AbstractApiController
{
    //override default status code
    const UNKNOWN_STATUS_CODE = 202;

    const AUTHENTICATION_TOKEN_INVALID = 'authentication token invalid';

    /**
     * @param Request $request
     * @param string $targetClass
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return bool
     */
    protected function parseAuthenticatedRequest(Request $request, $targetClass, &$authenticatedRequest, &$errorResponse, &$constructionManager)
    {
        /** @var AuthenticatedRequest $authenticatedRequest */
        if (!parent::parseRequest($request, $targetClass, $authenticatedRequest, $errorResponse)) {
            return false;
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($authenticatedRequest);
        if (null === $constructionManager) {
            $errorResponse = $this->fail(self::AUTHENTICATION_TOKEN_INVALID);

            return false;
        }

        return true;
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
        switch ($message) {
            case self::AUTHENTICATION_TOKEN_INVALID:
                return 2;
        }

        return parent::errorMessageToStatusCode($message);
    }
}
