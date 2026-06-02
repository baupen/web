<?php

namespace App\Api\Serializer;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Processor\SerializeProcessor;
use ApiPlatform\State\ProcessorInterface;
use App\Controller\Traits\FileResponseTrait;
use App\Controller\Traits\ImageRequestTrait;
use App\Entity\Map;
use App\Security\TokenTrait;
use App\Service\Interfaces\FilterServiceInterface;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\Pdf\ReportElements;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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
        private PathServiceInterface $pathService
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Response
    {
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
