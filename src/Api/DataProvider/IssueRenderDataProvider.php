<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataProvider;

use App\Api\DataProvider\Base\NoPaginationDataProvider;
use App\Controller\Traits\FileResponseTrait;
use App\Controller\Traits\ImageRequestTrait;
use App\Entity\Issue;
use App\Entity\Map;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

class IssueRenderDataProvider extends NoPaginationDataProvider
{
    use FileResponseTrait;
    use ImageRequestTrait;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * @var ImageServiceInterface
     */
    private $imageService;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, ImageServiceInterface $imageService, PathServiceInterface $pathService, iterable $collectionExtensions = [])
    {
        parent::__construct($managerRegistry, $collectionExtensions);
        $this->requestStack = $requestStack;
        $this->manager = $managerRegistry;
        $this->imageService = $imageService;
        $this->pathService = $pathService;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return Issue::class === $resourceClass && 'get_render' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = [])
    {
        $existingFilter = isset($context['filters']) ? $context['filters'] : [];
        if (!isset($existingFilter['map']) || isset($existingFilter['map[]'])) {
            throw new BadRequestException('The map filter is not set.');
        }

        $currentRequest = $this->requestStack->getCurrentRequest();
        $size = $this->getValidImageSizeFromQuery($currentRequest->query);
        $currentRequest->attributes->set('size', $size);

        $map = $this->manager->getRepository(Map::class)->find($existingFilter['map']);
        if (null === $map || $map->getConstructionSite()->getId() !== $existingFilter['constructionSite'] || !$map->getFile()) {
            throw new BadRequestException('The map does not exist, does not belong to the construction site, or has no file assigned.');
        }
        $currentRequest->attributes->set('map', $map);

        $queryBuilder = $this->getCollectionQueryBuilerWithoutPagination($resourceClass, $operationName, $context);

        return $queryBuilder->getQuery()->getResult();
    }
}
