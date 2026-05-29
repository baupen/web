<?php

namespace App\Api\CustomController;

use App\Controller\Traits\FileResponseTrait;
use App\Controller\Traits\ImageRequestTrait;
use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class IssuesRender
{
    use FileResponseTrait;
    use ImageRequestTrait;

    private RequestStack $requestStack;

    private ManagerRegistry $manager;

    private ImageServiceInterface $imageService;

    private PathServiceInterface $pathService;

    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, ImageServiceInterface $imageService, PathServiceInterface $pathService)
    {
        $this->requestStack = $requestStack;
        $this->manager = $managerRegistry;
        $this->imageService = $imageService;
        $this->pathService = $pathService;
    }

    /**
     * @param Issue[] $data
     */
    public function __invoke(array $data): BinaryFileResponse|Response
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        $size = $currentRequest->attributes->get('size');
        $map = $currentRequest->attributes->get('map');

        $path = $this->imageService->renderMapFileWithIssuesToJpg($map->getFile(), $data, $size);

        $emptyAnswer = $currentRequest->headers->has('X-EMPTY-RESPONSE-EXPECTED');
        if ($emptyAnswer) {
            return new Response();
        }

        return $this->tryCreateInlineFileResponse($path, 'render.jpg', false);
    }
}
