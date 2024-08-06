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

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Api\Entity\CraftsmanStatistics;
use App\Entity\Craftsman;
use App\Service\Interfaces\AnalysisServiceInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CraftsmanStatisticsDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider;

    private IriConverterInterface $iriConverter;

    private AnalysisServiceInterface $analysisService;

    private const ALREADY_CALLED = 'CRAFTSMAN_STATISTICS_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * CraftsmanStatisticsDataProvider constructor.
     */
    public function __construct(ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider, IriConverterInterface $iriConverter, SerializerInterface $serializer, AnalysisServiceInterface $analysisService)
    {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->iriConverter = $iriConverter;
        $this->analysisService = $analysisService;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return Craftsman::class === $resourceClass && 'get_statistics' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): iterable
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var Craftsman[] $craftsmen */
        $craftsmen = $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);

        $craftsmanAnalysisByCraftsman = $this->analysisService->createCraftsmanAnalysisByCraftsman($craftsmen);
        $statistics = [];
        foreach ($craftsmanAnalysisByCraftsman as $craftsmanId => $craftsmanAnalysis) {
            $craftsmanIri = $this->iriConverter->getIriFromResource(Craftsman::class, UrlGeneratorInterface::ABS_PATH, null, ['uri_variables' => ['id' => $craftsmanId]]);
            $statistics[] = CraftsmanStatistics::createFromCraftsmanAnalysis($craftsmanAnalysis, $craftsmanIri);
        }

        return $statistics;
    }
}
