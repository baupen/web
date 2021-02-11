<?php

/*
 * This file is part of the mangel.io project.
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class IssueRenderDataProvider extends NoPaginationDataProvider
{
    use FileResponseTrait;
    use ImageRequestTrait;

    /**
     * @var Request
     */
    private $request;

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
        $this->request = $requestStack->getCurrentRequest();
        $this->manager = $managerRegistry;
        $this->imageService = $imageService;
        $this->pathService = $pathService;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Issue::class === $resourceClass && 'get_render' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $existingFilter = isset($context['filters']) ? $context['filters'] : [];
        if (!isset($existingFilter['map']) || isset($existingFilter['map[]'])) {
            throw new BadRequestException();
        }

        $size = $this->getValidImageSizeFromQuery($this->request->query);

        $map = $this->manager->getRepository(Map::class)->find($existingFilter['map']);
        if (null === $map || $map->getConstructionSite()->getId() !== $existingFilter['constructionSite']) {
            throw new BadRequestException();
        }

        $queryBuilder = $this->getCollectionQueryBuilerWithoutPagination($resourceClass, $operationName, $context);
        $issues = $queryBuilder->getQuery()->getResult();

        $path = $this->imageService->renderMapFileWithIssuesToJpg($map->getFile(), $issues, $size);

        return $this->tryCreateInlineFileResponse($path, 'render.jpg');
    }
}
