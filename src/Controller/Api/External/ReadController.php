<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\External;

use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\ReadRequest;
use App\Api\External\Response\Data\ReadData;
use App\Api\External\Transformer\TransformerFactory;
use App\Controller\Api\External\Base\ExternalApiController;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Map;
use App\Entity\Traits\IdTrait;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/read")
 *
 * @return Response
 */
class ReadController extends ExternalApiController
{
    const MAX_VARIABLE_NUMBER = 900;

    /**
     * @Route("", name="api_external_read", methods={"POST"})
     *
     * @throws Exception
     * @throws ORMException
     *
     * @return Response
     */
    public function readAction(Request $request, TransformerFactory $transformerFactory)
    {
        /** @var ReadRequest $readRequest */
        /** @var ConstructionManager $constructionManager */
        if (!$this->parseAuthenticatedRequest($request, ReadRequest::class, $readRequest, $errorResponse, $constructionManager)) {
            return $errorResponse;
        }

        //construct read data
        $readData = new ReadData();
        if ($constructionManager->getLastChangedAt() > new DateTime($readRequest->getUser()->getLastChangeTime())) {
            $readData->setChangedUser($transformerFactory->getUserTransformer()->toApi($constructionManager, $readRequest->getAuthenticationToken()));
        }
        $readData->setChangedConstructionSites([]);
        $readData->setChangedCraftsmen([]);
        $readData->setChangedIssues([]);
        $readData->setChangedMaps([]);
        $readData->setRemovedConstructionSiteIDs([]);
        $readData->setRemovedCraftsmanIDs([]);
        $readData->setRemovedIssueIDs([]);
        $readData->setRemovedMapIDs([]);

        //process changes
        $this->processObjectMeta($transformerFactory, $readRequest, $constructionManager, $readData);

        //construct answer
        return $this->success($readData);
    }

    /**
     * @throws ORMException
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    private function processObjectMeta(TransformerFactory $transformerFactory, ReadRequest $readRequest, ConstructionManager $constructionManager, ReadData $readData)
    {
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();
        $incompleteManager = $manager->create(
            $manager->getConnection(),
            $manager->getConfiguration()
        );

        //## BUILDING ###
        //prepare mapping builder
        $resultSetMapping = new ResultSetMappingBuilder($incompleteManager);
        $resultSetMapping->addRootEntityFromClassMetadata(ConstructionSite::class, 's');
        $resultSetMapping->addFieldResult('s', 'id', 'id');

        //get allowed building ids
        $sql = 'SELECT DISTINCT s.id FROM construction_site s INNER JOIN construction_site_construction_manager cscm ON cscm.construction_site_id = s.id
WHERE cscm.construction_manager_id = :id';
        $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
        $query->setParameters(['id' => $constructionManager->getId()]);
        /** @var IdTrait[] $validConstructionSites */
        $validConstructionSites = $query->getResult();
        $this->filterIds($readRequest->getConstructionSites(), $validConstructionSites, $allValidIds, $removeIds, $knownIds);

        //set the removed/invalid buildings
        $readData->setRemovedConstructionSiteIDs(array_keys($removeIds));

        //if no access to any buildings do an early return
        if (\count($allValidIds) === 0) {
            return;
        }

        //get updated / new buildings
        $retrieveConstructionSiteIds = $this->getUpdated('SELECT DISTINCT s.id FROM construction_site s WHERE', 's', $allValidIds, $knownIds, $resultSetMapping, $incompleteManager);

        $readData->setChangedConstructionSites(
            $transformerFactory->getBuildingTransformer()->toApiMultiple(
                $manager->getRepository(ConstructionSite::class)->findBy(['id' => $retrieveConstructionSiteIds])
            )
        );
        $validConstructionSiteIds = $allValidIds;

        //## craftsman ###
        //prepare mapping builder
        $resultSetMapping = new ResultSetMappingBuilder($incompleteManager);
        $resultSetMapping->addRootEntityFromClassMetadata(Craftsman::class, 'c');
        $resultSetMapping->addFieldResult('c', 'id', 'id');

        //get allowed craftsman ids
        $validCraftsmen = $this->executeIdInQuery('SELECT DISTINCT c.id FROM craftsman c WHERE c.construction_site_id', $validConstructionSiteIds, $resultSetMapping, $incompleteManager);
        $this->filterIds($readRequest->getCraftsmen(), $validCraftsmen, $allValidIds, $removeIds, $knownIds);

        //set the removed/invalid craftsmen
        $readData->setRemovedCraftsmanIDs(array_keys($removeIds));

        //get updated / new craftsmen
        $retrieveCraftsmanIds = $this->getUpdated('SELECT DISTINCT c.id FROM craftsman c WHERE', 'c', $allValidIds, $knownIds, $resultSetMapping, $incompleteManager);

        $readData->setChangedCraftsmen(
            $transformerFactory->getCraftsmanTransformer()->toApiMultiple(
                $manager->getRepository(Craftsman::class)->findBy(['id' => $retrieveCraftsmanIds])
            )
        );

        //## maps ###
        //prepare mapping builder
        $resultSetMapping = new ResultSetMappingBuilder($incompleteManager);
        $resultSetMapping->addRootEntityFromClassMetadata(Map::class, 'm');
        $resultSetMapping->addFieldResult('m', 'id', 'id');

        //get allowed craftsman ids
        $validMaps = $this->executeIdInQuery('SELECT DISTINCT m.id FROM map m WHERE m.construction_site_id', $validConstructionSiteIds, $resultSetMapping, $incompleteManager);
        $this->filterIds($readRequest->getMaps(), $validMaps, $allValidIds, $removeIds, $knownIds);

        //set the removed/invalid maps
        $readData->setRemovedMapIDs(array_keys($removeIds));

        //get updated / new maps
        $retrieveMapIds = $this->getUpdated('SELECT DISTINCT m.id FROM map m WHERE', 'm', $allValidIds, $knownIds, $resultSetMapping, $incompleteManager);

        $readData->setChangedMaps(
            $transformerFactory->getMapTransformer()->toApiMultiple(
                $manager->getRepository(Map::class)->findBy(['id' => $retrieveMapIds])
            )
        );
        $validMapIds = $allValidIds;

        //## issues ###
        //prepare mapping builder
        $resultSetMapping = new ResultSetMappingBuilder($incompleteManager);
        $resultSetMapping->addRootEntityFromClassMetadata(Issue::class, 'i');
        $resultSetMapping->addFieldResult('i', 'id', 'id');

        //get allowed issue ids
        $validIssues = $this->executeIdInQuery('SELECT DISTINCT i.id FROM issue i WHERE i.map_id', $validMapIds, $resultSetMapping, $incompleteManager);
        $this->filterIds($readRequest->getIssues(), $validIssues, $allValidIds, $removeIds, $knownIds);

        //set the removed/invalid issues
        $readData->setRemovedIssueIDs(array_keys($removeIds));

        //get updated / new issues
        $retrieveIssueIds = $this->getUpdated('SELECT DISTINCT i.id FROM issue i WHERE ', 'i', $allValidIds, $knownIds, $resultSetMapping, $incompleteManager);

        $readData->setChangedIssues(
            $transformerFactory->getIssueTransformer()->toApiMultiple(
                $manager->getRepository(Issue::class)->findBy(['id' => $retrieveIssueIds])
            )
        );
    }

    /**
     * break down id structure to some helper structures.
     *
     * @param ObjectMeta[] $requestObjectMeta the given ids
     * @param IdTrait[]    $dbEntities
     * @param string[]     $allValidIds       contains all ids from the db
     * @param string[]     $removeIds         contains the invalid given (ids -> time)
     * @param string[]     $knownIds          contains the valid given (id -> time)
     *
     * @throws Exception
     */
    private function filterIds($requestObjectMeta, $dbEntities, &$allValidIds, &$removeIds, &$knownIds)
    {
        $removeIds = [];
        foreach ($requestObjectMeta as $objectMeta) {
            $dateTime = new DateTime($objectMeta['lastChangeTime']);
            $dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            $removeIds[$objectMeta['id']] = $dateTime->format('Y-m-d H:i:s');
        }

        $allValidIds = [];
        $knownIds = [];
        foreach ($dbEntities as $validConstructionSite) {
            $validConstructionSiteId = $validConstructionSite->getId();
            if (isset($removeIds[$validConstructionSiteId])) {
                $knownIds[$validConstructionSiteId] = $removeIds[$validConstructionSiteId];
                unset($removeIds[$validConstructionSiteId]);
            }
            $allValidIds[] = $validConstructionSiteId;
        }
    }

    /**
     * @param $ids
     *
     * @return IdTrait[]
     */
    private function executeIdInQuery(string $baseQuery, array $ids, ResultSetMapping $resultSetMapping, EntityManager $incompleteManager)
    {
        $chuncks = array_chunk($ids, self::MAX_VARIABLE_NUMBER);
        $result = [];
        foreach ($chuncks as $chunck) {
            $sql = $baseQuery . ' IN ("' . implode('", "', $chunck) . '")';
            $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
            $result = array_merge($result, $query->getResult());
        }

        return $result;
    }

    /**
     * @param $allValidIds
     * @param $knownIds
     * @param mixed $tableShort
     *
     * @return IdTrait[]
     */
    private function getUpdated(string $baseQuery, string $tableShort, array $allValidIds, array $knownIds, ResultSetMappingBuilder $resultSetMapping, EntityManager $incompleteManager)
    {
        $result = [];
        $existing = [];
        foreach ($allValidIds as $validId) {
            if (isset($knownIds[$validId])) {
                $existing[$validId] = $knownIds[$validId];
            } else {
                $result[] = $validId;
            }
        }

        $chunks = array_chunk($existing, self::MAX_VARIABLE_NUMBER / 2, true);
        /** @var IdTrait[] $existingResult */
        $existingResult = [];
        foreach ($chunks as $chunk) {
            //only return entries if they are updated
            $parameters = [];
            $counter = 0;
            $sqlEntries = [];
            foreach (array_keys($chunk) as $guid) {
                // id matches but last change date is bigger
                $sqlEntries[] = '(' . $tableShort . '.id == :guid_' . $counter . ' AND ' . $tableShort . '.last_changed_at > :time_' . $counter . ')';
                $parameters['time_' . $counter] = $knownIds[$guid];
                $parameters['guid_' . $counter] = $guid;

                ++$counter;
            }

            $sql = $baseQuery . ' ' . implode(' OR ', $sqlEntries);

            //execute query for updated
            $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
            $query->setParameters($parameters);

            /** @var IdTrait[] $updatedCraftsmen */
            $existingResult = array_merge($existingResult, $query->getResult());
        }

        foreach ($existingResult as $entry) {
            $result[] = $entry->getId();
        }

        return $result;
    }
}
