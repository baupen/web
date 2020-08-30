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

use App\Api\External\Request\LoginRequest;
use App\Api\External\Response\Data\LoginData;
use App\Api\External\Transformer\UserTransformer;
use App\Controller\Api\External\Base\ExternalApiController;
use App\Entity\AuthenticationToken;
use App\Entity\ConstructionManager;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/login")
 *
 * @return Response
 */
class LoginController extends ExternalApiController
{
    const UNKNOWN_USERNAME = 'unknown username';
    const WRONG_PASSWORD = 'wrong password';

    /**
     * @Route("", name="api_external_login", methods={"POST"})
     *
     * @throws Exception
     *
     * @return Response
     */
    public function loginAction(Request $request, UserTransformer $userTransformer)
    {
        /** @var LoginRequest|Response $loginRequest */
        if (!$this->parseRequest($request, LoginRequest::class, $loginRequest, $errorResponse)) {
            return $errorResponse;
        }

        //check username & password
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['email' => $loginRequest->getUsername()]);
        if (null === $constructionManager) {
            return $this->fail(static::UNKNOWN_USERNAME);
        }
        if ($constructionManager->getPasswordHash() !== $loginRequest->getPasswordHash()) {
            return $this->fail(static::WRONG_PASSWORD);
        }

        //create auth token
        $authToken = AuthenticationToken::createFor($constructionManager);
        $this->fastSave($authToken);

        //construct answer
        $user = $userTransformer->toApi($constructionManager, $authToken->getToken());
        $loginData = new LoginData($user);

        return $this->success($loginData);
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
            case static::UNKNOWN_USERNAME:
                return 100;
            case static::WRONG_PASSWORD:
                return 101;
        }

        return parent::errorMessageToStatusCode($message);
    }
}
