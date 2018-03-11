<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Api\Request\LoginRequest;
use App\Api\Response\Base\BaseResponse;
use App\Api\Response\LoginResponse;
use App\Controller\Base\BaseDoctrineController;
use App\Controller\Base\BaseFormController;
use App\Entity\AppUser;
use App\Entity\FrontendUser;
use App\Enum\ApiStatus;
use App\Form\Model\ContactRequest\ContactRequestType;
use App\Model\ContactRequest;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\SerializerTest;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/api")
 *
 * @return Response
 */
class ApiController extends BaseDoctrineController
{

    /**
     * inject the translator service
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + ['translator' => TranslatorInterface::class];
    }

    /**
     * @Route("/login", name="api_login")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function loginAction(Request $request, SerializerInterface $serializer)
    {
        if (!($content = $request->getContent())) {
            return $this->failed(ApiStatus::EMPTY_REQUEST);
        }
        /* @var LoginRequest $loginRequest */
        $loginRequest = $serializer->deserialize($content, LoginRequest::class, "json");

        $user = $this->getDoctrine()->getRepository(AppUser::class)->findOneBy(["identifier" => $loginRequest->getIdentifier()]);
        if ($user === null) {
            return $this->failed(ApiStatus::UNKNOWN_IDENTIFIER);
        }
        if ($user->getPasswordHash() !== $loginRequest->getPasswordHash()) {
            return $this->failed(ApiStatus::WRONG_PASSWORD);
        }

        $user->setAuthenticationToken();
        $this->fastSave($user);

        $loginResponse = new LoginResponse();
        $loginResponse->setUser($user);
        return $this->json($loginResponse);
    }

    /**
     * @param ApiStatus|int $apiError
     * @return JsonResponse
     */
    private function failed($apiError)
    {
        $response = new BaseResponse();
        $response->setApiStatus($apiError);
        $response->setApiErrorMessage(ApiStatus::getTranslationForValue($apiError, $this->get("translator")));

        return $this->json($response);
    }
}
