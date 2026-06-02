<?php

namespace App\Api\Serializer;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Controller\Traits\FileResponseTrait;
use App\Controller\Traits\ImageRequestTrait;
use App\Entity\Map;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[AsDecorator(decorates: 'api_platform.state_processor.serialize')]
readonly class IssueRenderSerializer implements ProcessorInterface
{
    use FileResponseTrait;
    use ImageRequestTrait;

    public function __construct(
        #[AutowireDecorated]
        private ProcessorInterface $decorated,
        private ManagerRegistry $manager,
        private RequestStack $requestStack,
        private ImageServiceInterface $imageService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        /** @var HttpOperation $operation */
        if ($operation->getUriTemplate() !== '/issues/render.jpg') {
            return $this->decorated->process($data, $operation, $uriVariables, $context);
        }

        $currentRequest = $this->requestStack->getCurrentRequest();
        $size = $this->getValidImageSizeFromQuery($currentRequest->query);

        $filters = $currentRequest->attributes->get('filters');
        $mapId = $filters['map'] ?? null;
        $map = $mapId ? $this->manager->getRepository(Map::class)->find($mapId) : null;
        if (!$map) {
            throw new BadRequestException('The map is not set.');
        }

        $path = $this->imageService->renderMapFileWithIssuesToJpg($map->getFile(), $data, $size);

        $emptyAnswer = $currentRequest->headers->has('X-EMPTY-RESPONSE-EXPECTED');
        if ($emptyAnswer) {
            return new Response();
        }

        return $this->tryCreateInlineFileResponse($path, 'render.jpg', false);
    }
}
