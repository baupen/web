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

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Api\Entity\FeedEntry;
use App\Entity\ConstructionSite;
use App\Service\FeedService;
use Doctrine\Persistence\ManagerRegistry;

class FeedEntryDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var FeedService
     */
    private $feedService;

    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * FeedEntryDataProvider constructor.
     */
    public function __construct(FeedService $feedService, ManagerRegistry $manager)
    {
        $this->feedService = $feedService;
        $this->manager = $manager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return FeedEntry::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $filters = $context['filters'];

        $constructionSiteId = $filters['constructionSite'];
        $constructionSiteRepo = $this->manager->getRepository(ConstructionSite::class);
        $constructionSite = $constructionSiteRepo->find($constructionSiteId);

        return $this->feedService->getFeedEntries($constructionSite);
    }
}
