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

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Api\Entity\CraftsmanStatistics;
use App\Entity\Craftsman;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class CraftsmanStatisticsDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var ContextAwareCollectionDataProviderInterface
     */
    private $decoratedCollectionDataProvider;

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    private const ALREADY_CALLED = 'CRAFTSMAN_STATISTICS_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * CraftsmanStatisticsDataProvider constructor.
     */
    public function __construct(ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider, IriConverterInterface $iriConverter, SerializerInterface $serializer)
    {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->iriConverter = $iriConverter;
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

    /**
     * @param Craftsman[] $craftsmen
     *
     * @return CraftsmanStatistics[]
     */
    private function createStatistics(array $craftsmen): array
    {
        $statistics = [];
        foreach ($craftsmen as $craftsman) {
            $statistic = new CraftsmanStatistics();

            $iri = $this->iriConverter->getIriFromItem($craftsman);
            $statistic->setCraftsman($iri);

            $statistics[] = $statistic;
        }

        return $statistics;
    }
}
