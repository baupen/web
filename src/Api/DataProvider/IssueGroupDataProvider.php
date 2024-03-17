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

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Api\DataProvider\Base\NoPaginationDataProvider;
use App\Api\Entity\IssueGroup;
use App\Controller\Traits\FileResponseTrait;
use App\Controller\Traits\ImageRequestTrait;
use App\Doctrine\UTCDateTimeType;
use App\Entity\Issue;
use App\Entity\Map;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

class IssueGroupDataProvider extends NoPaginationDataProvider
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, SerializerInterface $serializer, IriConverterInterface $iriConverter, iterable $collectionExtensions = [])
    {
        parent::__construct($managerRegistry, $collectionExtensions);
        $this->requestStack = $requestStack;
        $this->manager = $managerRegistry;
        $this->serializer = $serializer;
        $this->iriConverter = $iriConverter;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return Issue::class === $resourceClass && 'get_group' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = [])
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        $group = $currentRequest->query->get('group');
        if ('map' !== $group) {
            throw new BadRequestException('The group '.$group.' is unexpected.');
        }

        $queryBuilder = $this->getCollectionQueryBuilerWithoutPagination($resourceClass, $operationName, $context);
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $validIssueIdResult = $queryBuilder->addSelect($rootAlias.'.id')->getQuery()->getResult();
        $validIssueIds = [];
        foreach ($validIssueIdResult as $entry) {
            $validIssueIds[] = $entry['id'];
        }

        $issueRepository = $this->manager->getRepository(Issue::class);
        $groupByQuery = $issueRepository
            ->createQueryBuilder('i')
            ->addSelect(['IDENTITY(i.map)', 'COUNT(i)', 'MIN(i.deadline)'])
            ->where('i.id IN (:ids)')
            ->setParameter(':ids', $validIssueIds)
            ->groupBy('i.map');

        $issueGroupResults = $groupByQuery->getQuery()->getResult();
        $issueGroups = [];
        foreach ($issueGroupResults as $issueGroupResult) {
            // indexes are 1-based
            $iri = $this->iriConverter->getItemIriFromResourceClass(Map::class, ['id' => $issueGroupResult[1]]);
            $count = $issueGroupResult[2];
            $earliestDeadline = UTCDateTimeType::tryParseDateTime($issueGroupResult[3]);
            $issueGroups[] = IssueGroup::create($iri, $count, $earliestDeadline);
        }

        return $issueGroups;
    }
}
