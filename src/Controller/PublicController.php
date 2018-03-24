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


use App\Api\ApiSerializable;
use App\Api\Request\Base\BaseRequest;
use App\Api\Request\DownloadFileRequest;
use App\Api\Request\LoginRequest;
use App\Api\Response\Base\BaseResponse;
use App\Api\Response\LoginResponse;
use App\Api\Request\SyncRequest;
use App\Api\Response\SyncResponse;
use App\Controller\Base\BaseDoctrineController;
use App\Controller\Base\BaseFormController;
use App\Entity\AppUser;
use App\Entity\Building;
use App\Entity\BuildingMap;
use App\Entity\Craftsman;
use App\Entity\FrontendUser;
use App\Entity\Marker;
use App\Entity\Traits\IdTrait;
use App\Enum\ApiStatus;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/public")
 *
 * @return Response
 */
class PublicController extends BaseDoctrineController
{

    /**
     * @Route("/{guid}", name="public_view")
     * @param $guid
     */
    public function viewAction($guid)
    {

    }
}
