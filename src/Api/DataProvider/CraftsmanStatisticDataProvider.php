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
use App\Entity\Craftsman;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class CraftsmanStatisticDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var ContextAwareCollectionDataProviderInterface
     */
    private $decoratedCollectionDataProvider;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    private const ALREADY_CALLED = 'CRAFTSMAN_STATISTICS_DATA_PROVIDER_ALREADY_CALLED';

    public function __construct(ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider, SerializerInterface $serializer)
    {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->serializer = $serializer;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return Craftsman::class === $resourceClass && 'get_statistics' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var Craftsman[] $craftsmen */
        $craftsmen = $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);

        $statistics = $this->createStatistics($craftsmen);

        $json = $this->serializer->serialize($statistics, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    private function createStatistics(array $craftsmen): array
    {
        return [];
    }
}
