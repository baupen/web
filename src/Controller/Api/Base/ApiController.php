<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\Base;

use App\Api\Request\ConstructionSiteRequest;
use App\Entity\ConstructionSite;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\HttpFoundation\Request;

abstract class ApiController extends AbstractApiController
{
    //override default status code
    const UNKNOWN_STATUS_CODE = 1;

    const CONSTRUCTION_SITE_NOT_FOUND = 'site not found';
    const ACCESS_DENIED = 'access denied';

    /**
     * @param Request $request
     * @param string $targetClass
     * @param $parsedRequest
     * @param $errorResponse
     * @param $constructionSite
     *
     * @return bool
     */
    protected function parseConstructionSiteRequest(Request $request, $targetClass, &$parsedRequest, &$errorResponse, &$constructionSite)
    {
        /** @var ConstructionSiteRequest $parsedRequest */
        if (!parent::parseRequest($request, $targetClass, $parsedRequest, $errorResponse)) {
            return false;
        }

        //check auth token
        /** @var ConstructionSite $constructionSite */
        $constructionSite = $this->getDoctrine()->getRepository(ConstructionSite::class)->find($parsedRequest->getConstructionSiteId());
        if ($constructionSite === null) {
            $errorResponse = $this->fail(self::CONSTRUCTION_SITE_NOT_FOUND);

            return false;
        }

        if (!$this->isGranted(BaseVoter::ANY_ATTRIBUTE, $constructionSite)) {
            $errorResponse = $this->fail(self::ACCESS_DENIED);

            return false;
        }

        return true;
    }

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        switch ($message) {
            case static::CONSTRUCTION_SITE_NOT_FOUND:
                return 2;
        }

        return parent::errorMessageToStatusCode($message);
    }
}
