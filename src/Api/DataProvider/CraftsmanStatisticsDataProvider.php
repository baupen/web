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
use App\Doctrine\UTCDateTimeType;
use App\Entity\Craftsman;
use App\Entity\Issue;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
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

    /**
     * @var ManagerRegistry
     */
    private $manager;

    private const ALREADY_CALLED = 'CRAFTSMAN_STATISTICS_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * CraftsmanStatisticsDataProvider constructor.
     */
    public function __construct(ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider, IriConverterInterface $iriConverter, SerializerInterface $serializer, ManagerRegistry $manager)
    {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->iriConverter = $iriConverter;
        $this->serializer = $serializer;
        $this->manager = $manager;
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
        $statisticsDictionary = [];
        foreach ($craftsmen as $craftsman) {
            $statistic = new CraftsmanStatistics();

            $iri = $this->iriConverter->getIriFromItem($craftsman);
            $statistic->setCraftsman($iri);

            $statisticsDictionary[$craftsman->getId()] = $statistic;
        }

        $this->createIssueSummary($craftsmen, $statisticsDictionary);

        $this->countUnreadIssues($craftsmen, $statisticsDictionary);
        $this->countOverdueIssues($craftsmen, $statisticsDictionary);

        $this->findNextDeadline($craftsmen, $statisticsDictionary);
        $this->findLastIssueResolved($craftsmen, $statisticsDictionary);
        $this->findLastActivity($craftsmen, $statisticsDictionary);

        return array_values($statisticsDictionary);
    }

    private function createIssueSummary(array $craftsmen, array $statisticsDictionary)
    {
        $issueRepository = $this->manager->getRepository(Issue::class);

        $rootAlias = 'i';
        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder($rootAlias, $craftsmen);

        $queryBuilderOpenIssues = $issueRepository->filterOpenIssues($rootAlias, clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderOpenIssues, $statisticsDictionary, 'COUNT(i)',
            function (CraftsmanStatistics $statistics, $value) {
                $statistics->getIssueSummary()->setOpenCount($value);
            }
        );

        $queryBuilderResolvedIssues = $issueRepository->filterInspectableIssues($rootAlias, clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderResolvedIssues, $statisticsDictionary, 'COUNT(i)',
            function (CraftsmanStatistics $statistics, $value) {
                $statistics->getIssueSummary()->setInspectableCount($value);
            }
        );

        $queryBuilderClosedIssues = $issueRepository->filterClosedIssues($rootAlias, clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderClosedIssues, $statisticsDictionary, 'COUNT(i)',
            function (CraftsmanStatistics $statistics, $value) {
                $statistics->getIssueSummary()->setClosedCount($value);
            }
        );
    }

    /**
     * @param Craftsman[]           $craftsmen
     * @param CraftsmanStatistics[] $statisticsDictionary
     */
    private function countUnreadIssues(array $craftsmen, array $statisticsDictionary)
    {
        $queryBuilder = $this->getOpenCraftsmanIssuesQueryBuilder('i', $craftsmen)
            ->join('i.craftsman', 'c')
            ->andWhere('i.registeredAt > c.lastVisitOnline OR c.lastVisitOnline IS NULL');

        $this->groupByCraftsmanAndEvaluate(
            $queryBuilder, $statisticsDictionary, 'COUNT(i)',
            function (CraftsmanStatistics $statistics, $value) {
                $statistics->setIssueUnreadCount($value);
            }
        );
    }

    /**
     * @param Craftsman[]           $craftsmen
     * @param CraftsmanStatistics[] $statisticsDictionary
     */
    private function countOverdueIssues(array $craftsmen, array $statisticsDictionary)
    {
        $queryBuilder = $this->getOpenCraftsmanIssuesQueryBuilder('i', $craftsmen)
            ->andWhere('i.deadline IS NOT NULL')
            ->andWhere('i.deadline < :now')
            ->setParameter(':now', new \DateTime());

        $this->groupByCraftsmanAndEvaluate(
            $queryBuilder, $statisticsDictionary, 'COUNT(i)',
            function (CraftsmanStatistics $statistics, $value) {
                $statistics->setIssueOverdueCount($value);
            }
        );
    }

    /**
     * @param Craftsman[]           $craftsmen
     * @param CraftsmanStatistics[] $statisticsDictionary
     */
    private function findNextDeadline(array $craftsmen, array $statisticsDictionary)
    {
        $queryBuilder = $this->getOpenCraftsmanIssuesQueryBuilder('i', $craftsmen)
            ->andWhere('i.deadline IS NOT NULL');

        $this->groupByCraftsmanAndEvaluate(
            $queryBuilder, $statisticsDictionary, 'MIN(i.deadline)',
            function (CraftsmanStatistics $statistics, $value) {
                $statistics->setNextDeadline(UTCDateTimeType::tryParseDateTime($value));
            }
        );
    }

    /**
     * @param Craftsman[]           $craftsmen
     * @param CraftsmanStatistics[] $statisticsDictionary
     */
    private function findLastIssueResolved(array $craftsmen, array $statisticsDictionary)
    {
        $rootAlias = 'i';
        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder($rootAlias, $craftsmen)
            ->andWhere($rootAlias.'.registeredAt IS NOT NULL');

        $this->groupByCraftsmanAndEvaluate(
            $queryBuilder, $statisticsDictionary, 'MAX(i.resolvedAt)',
            function (CraftsmanStatistics $statistics, $value) {
                $statistics->setLastIssueResolved(UTCDateTimeType::tryParseDateTime($value));
            }
        );
    }

    private function getCraftsmanIssuesQueryBuilder(string $rootAlias, array $craftsmen)
    {
        $craftsmanIds = $this->getCraftsmanIds($craftsmen);

        $issueRepository = $this->manager->getRepository(Issue::class);

        return $issueRepository->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias.'.deletedAt IS NULL')
            ->andWhere($rootAlias.'.craftsman IN (:craftsmanIds)')
            ->setParameter(':craftsmanIds', $craftsmanIds);
    }

    private function getOpenCraftsmanIssuesQueryBuilder(string $rootAlias, array $craftsmen)
    {
        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder($rootAlias, $craftsmen);

        $issueRepository = $this->manager->getRepository(Issue::class);
        $issueRepository->filterOpenIssues($rootAlias, $queryBuilder);

        return $queryBuilder;
    }

    private function groupByCraftsmanAndEvaluate(QueryBuilder $queryBuilder, array $statisticsDictionary, string $selectExpression, \Closure $processResult)
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->groupBy($rootAlias.'.craftsman')
            ->select('identity('.$rootAlias.'.craftsman)')
            ->addSelect($selectExpression);

        $nextDeadlineResult = $queryBuilder->getQuery()->getResult();

        foreach ($nextDeadlineResult as $entry) {
            list($craftsmanId, $value) = array_values($entry);
            $processResult($statisticsDictionary[$craftsmanId], $value);
        }
    }

    /**
     * @param Craftsman[]           $craftsmen
     * @param CraftsmanStatistics[] $statisticsDictionary
     */
    private function findLastActivity(array $craftsmen, array $statisticsDictionary)
    {
        foreach ($craftsmen as $craftsman) {
            $statisticsDictionary[$craftsman->getId()]->setLastEmailReceived($craftsman->getLastEmailReceived());
            $statisticsDictionary[$craftsman->getId()]->setLastVisitOnline($craftsman->getLastVisitOnline());
        }
    }

    /**
     * @param Craftsman[] $craftsmen
     *
     * @return string[]
     */
    private function getCraftsmanIds(array $craftsmen): array
    {
        $craftsmanIds = [];
        foreach ($craftsmen as $craftsman) {
            $craftsmanIds[] = $craftsman->getId();
        }

        return $craftsmanIds;
    }
}
