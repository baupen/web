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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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
    /**
     * @Route("", name="api_external_read", methods={"POST"})
     *
     * @param Request $request
     * @param TransformerFactory $transformerFactory
     *
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
        if ($constructionManager->getLastChangedAt() > new \DateTime($readRequest->getUser()->getLastChangeTime())) {
            $readData->setChangedUser($transformerFactory->getUserTransformer()->toApi($constructionManager, $readRequest->getAuthenticationToken()));
        }
        $readData->setChangedBuildings([]);
        $readData->setChangedCraftsmen([]);
        $readData->setChangedIssues([]);
        $readData->setChangedMaps([]);
        $readData->setRemovedBuildingIDs([]);
        $readData->setRemovedCraftsmanIDs([]);
        $readData->setRemovedIssueIDs([]);
        $readData->setRemovedMapIDs([]);

        //process changes
        $this->processObjectMeta($transformerFactory, $readRequest, $constructionManager, $readData);

        //construct answer
        return $this->success($readData);
    }

    /**
     * @param TransformerFactory $transformerFactory
     * @param ReadRequest $readRequest
     * @param ConstructionManager $constructionManager
     * @param ReadData $readData
     *
     * @throws ORMException
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
        $this->filterIds($readRequest->getBuildings(), $validConstructionSites, $allValidIds, $removeIds, $knownIds);

        //set the removed/invalid buildings
        $readData->setRemovedBuildingIDs(array_keys($removeIds));

        //if no access to any buildings do an early return
        if (0 === count($allValidIds)) {
            return;
        }

        //get updated / new buildings
        $sql = 'SELECT DISTINCT s.id FROM construction_site s WHERE s.id IN ("' . implode('", "', $allValidIds) . '")';
        $parameters = [];
        $this->addUpdateUnknownConditions($parameters, $sql, $knownIds, 's');

        //execute query for updated
        $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
        $query->setParameters($parameters);
        /** @var IdTrait[] $updatedConstructionSites */
        $updatedConstructionSites = $query->getResult();

        //collect ids to retrieve
        $retrieveConstructionSiteIds = [];
        foreach ($updatedConstructionSites as $object) {
            $retrieveConstructionSiteIds[] = $object->getId();
        }

        $readData->setChangedBuildings(
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
        $sql = 'SELECT DISTINCT c.id FROM craftsman c WHERE c.construction_site_id IN ("' . implode('", "', $validConstructionSiteIds) . '")';
        $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
        /** @var IdTrait[] $validCraftsmen */
        $validCraftsmen = $query->getResult();
        $this->filterIds($readRequest->getCraftsmen(), $validCraftsmen, $allValidIds, $removeIds, $knownIds);

        //set the removed/invalid craftsmen
        $readData->setRemovedCraftsmanIDs(array_keys($removeIds));

        //get updated / new craftsmen
        $sql = 'SELECT DISTINCT c.id FROM craftsman c WHERE c.id IN ("' . implode('", "', $allValidIds) . '")';
        $parameters = [];
        $this->addUpdateUnknownConditions($parameters, $sql, $knownIds, 'c');

        //execute query for updated
        $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
        $query->setParameters($parameters);
        /** @var IdTrait[] $updatedCraftsmen */
        $updatedCraftsmen = $query->getResult();

        //collect ids to retrieve
        $retrieveCraftsmanIds = [];
        foreach ($updatedCraftsmen as $object) {
            $retrieveCraftsmanIds[] = $object->getId();
        }

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
        $sql = 'SELECT DISTINCT m.id FROM map m WHERE m.construction_site_id IN ("' . implode('", "', $validConstructionSiteIds) . '")';
        $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
        /** @var IdTrait[] $validMaps */
        $validMaps = $query->getResult();
        $this->filterIds($readRequest->getMaps(), $validMaps, $allValidIds, $removeIds, $knownIds);

        //set the removed/invalid maps
        $readData->setRemovedMapIDs(array_keys($removeIds));

        //get updated / new maps
        $sql = 'SELECT DISTINCT m.id FROM map m WHERE m.id IN ("' . implode('", "', $allValidIds) . '")';
        $parameters = [];
        $this->addUpdateUnknownConditions($parameters, $sql, $knownIds, 'm');

        //execute query for updated
        $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
        $query->setParameters($parameters);
        /** @var IdTrait[] $updatedMaps */
        $updatedMaps = $query->getResult();

        //collect ids to retrieve
        $retrieveMapIds = [];
        foreach ($updatedMaps as $object) {
            $retrieveMapIds[] = $object->getId();
        }

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

        //get allowed craftsman ids
        $sql = 'SELECT DISTINCT i.id FROM issue i WHERE i.map_id IN ("' . implode('", "', $validMapIds) . '")';
        $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
        /** @var IdTrait[] $validIssues */
        $validIssues = $query->getResult();
        $this->filterIds($readRequest->getIssues(), $validIssues, $allValidIds, $removeIds, $knownIds);

        //set the removed/invalid issues
        $readData->setRemovedIssueIDs(array_keys($removeIds));

        //get updated / new issues
        $sql = 'SELECT DISTINCT i.id FROM issue i WHERE i.id IN ("' . implode('", "', $allValidIds) . '")';
        $parameters = [];
        $this->addUpdateUnknownConditions($parameters, $sql, $knownIds, 'i');

        //execute query for updated
        $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
        $query->setParameters($parameters);
        /** @var IdTrait[] $updatedIssues */
        $updatedIssues = $query->getResult();

        //collect ids to retrieve
        $retrieveIssueIds = [];
        foreach ($updatedIssues as $object) {
            $retrieveIssueIds[] = $object->getId();
        }

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
     * @param IdTrait[] $dbEntities
     * @param string[] $allValidIds contains all ids from the db
     * @param string[] $removeIds contains the invalid given (ids -> time)
     * @param string[] $knownIds contains the valid given (id -> time)
     */
    private function filterIds($requestObjectMeta, $dbEntities, &$allValidIds, &$removeIds, &$knownIds)
    {
        $removeIds = [];
        foreach ($requestObjectMeta as $objectMeta) {
            $removeIds[$objectMeta['id']] = $objectMeta['lastChangeTime'];
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
     * append to query the add/update condition.
     *
     * @param $parameters
     * @param $sql
     * @param $guidTimeDictionary
     * @param $tableShort
     */
    private function addUpdateUnknownConditions(&$parameters, &$sql, $guidTimeDictionary, $tableShort)
    {
        $sql .= ' AND (';
        //only return confirmed buildings if they are updated
        if (count($guidTimeDictionary) > 0) {
            $sql .= '(';

            //get all where id matches but last change date does not
            $whereCondition = '';
            $parameters = [];
            $counter = 0;
            foreach (array_keys($guidTimeDictionary) as $confirmedBuildingId) {
                if (mb_strlen($whereCondition) > 0) {
                    $whereCondition .= ' OR ';
                }
                $whereCondition .= '(' . $tableShort . '.id == "' . $confirmedBuildingId . '" AND ' . $tableShort . '.last_changed_at > :time_' . $counter . ')';
                $parameters['time_' . $counter] = $guidTimeDictionary[$confirmedBuildingId];

                ++$counter;
            }
            $sql .= $whereCondition;
            $sql .= ') OR ';
        }

        //return buildings unknown to the requester
        if (count($guidTimeDictionary) > 0) {
            $sql .= $tableShort . '.id NOT IN ("' . implode('", "', array_keys($guidTimeDictionary)) . '")';
        } else {
            //allow all
            $sql .= '1 = 1';
        }

        $sql .= ')';
    }
}
