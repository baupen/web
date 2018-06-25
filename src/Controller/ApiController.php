<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Api\Entity\ObjectMeta;
use App\Api\Request\DownloadFileRequest;
use App\Api\Request\IssueActionRequest;
use App\Api\Request\IssueModifyRequest;
use App\Api\Request\LoginRequest;
use App\Api\Request\ReadRequest;
use App\Api\Response\Data\EmptyData;
use App\Api\Response\Data\IssueData;
use App\Api\Response\Data\LoginData;
use App\Api\Response\Data\ReadData;
use App\Api\Response\ErrorResponse;
use App\Api\Response\FailResponse;
use App\Api\Response\SuccessfulResponse;
use App\Api\Transformer\IssueTransformer;
use App\Api\Transformer\TransformerFactory;
use App\Api\Transformer\UserTransformer;
use App\Controller\Base\BaseDoctrineController;
use App\Entity\AuthenticationToken;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Map;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 *
 * @return Response
 */
class ApiController extends BaseDoctrineController
{
    const EMPTY_REQUEST = 'request empty';
    const REQUEST_VALIDATION_FAILED = 'request validation failed, not all required fields are set';

    const UNKNOWN_USERNAME = 'unknown username';
    const WRONG_PASSWORD = 'wrong password';
    const AUTHENTICATION_TOKEN_INVALID = 'authentication token invalid';

    const ISSUE_GUID_ALREADY_IN_USE = 'guid already in use';

    const ISSUE_NOT_FOUND = 'issue was not found';
    const ISSUE_ACCESS_DENIED = 'issue access not allowed';
    const ISSUE_ACTION_NOT_ALLOWED = 'this action can not be executed on the entity';

    const MAP_NOT_FOUND = 'map was not found';
    const MAP_ACCESS_DENIED = 'map access not allowed';

    const CRAFTSMAN_NOT_FOUND = 'craftsman was not found';
    const CRAFTSMAN_ACCESS_DENIED = 'craftsman access not allowed';

    const MAP_CRAFTSMAN_NOT_ON_SAME_CONSTRUCTION_SITE = 'the craftsman does not work on the same construction site as the assigned map';

    const ENTITY_NOT_FOUND = 'entity was not found';
    const ENTITY_ACCESS_DENIED = 'you are not allowed to access this entity';
    const ENTITY_NO_DOWNLOADABLE_FILE = 'entity has no file to download';
    const ENTITY_FILE_NOT_FOUND = 'the server could not find the file of the entity';

    const ISSUE_FILE_UPLOAD_FAILED = 'the uploaded file could not be processes';
    const ISSUE_NO_FILE_TO_UPLOAD = 'no file could be found in the request, but one was expected';
    const ISSUE_NO_FILE_UPLOAD_EXPECTED = 'a file was uploaded, but not specified in the issue';
    const INVALID_TIMESTAMP = 'invalid timestamp';

    private function errorMessageToStatusCode($message)
    {
        switch ($message) {
            case static::EMPTY_REQUEST:
            case static::REQUEST_VALIDATION_FAILED:
            case static::ISSUE_FILE_UPLOAD_FAILED:
                return 1;
            case static::AUTHENTICATION_TOKEN_INVALID:
                return 2;
            case static::UNKNOWN_USERNAME:
                return 100;
            case static::WRONG_PASSWORD:
                return 101;
            case static::ISSUE_GUID_ALREADY_IN_USE:
                return 200;
            case static::ISSUE_NOT_FOUND:
                return 201;
            case static::ISSUE_ACTION_NOT_ALLOWED:
                return 203;
        }

        return 202;
    }

    /**
     * inject the translator service.
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + ['translator' => TranslatorInterface::class, 'logger' => LoggerInterface::class];
    }

    /**
     * @Route("/login", name="api_login")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserTransformer $userTransformer
     *
     * @return Response
     */
    public function loginAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, UserTransformer $userTransformer)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var LoginRequest $loginRequest */
        $loginRequest = $serializer->deserialize($content, LoginRequest::class, 'json');

        // check all properties defined
        $errors = $validator->validate($loginRequest);
        if (count($errors) > 0) {
            return $this->fail(static::REQUEST_VALIDATION_FAILED);
        }

        //check username & password
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['email' => $loginRequest->getUsername()]);
        if (null === $constructionManager) {
            return $this->fail(static::UNKNOWN_USERNAME);
        }
        if ($constructionManager->getPasswordHash() !== $loginRequest->getPasswordHash()) {
            return $this->fail(static::WRONG_PASSWORD);
        }

        //create auth token
        $authToken = new AuthenticationToken($constructionManager);
        $this->fastSave($authToken);

        //construct answer
        $user = $userTransformer->toApi($constructionManager, $authToken->getToken());
        $loginData = new LoginData($user);

        return $this->success($loginData);
    }

    /**
     * @Route("/read", name="api_read")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param TransformerFactory $transformerFactory
     *
     * @throws ORMException
     *
     * @return Response
     */
    public function readAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, TransformerFactory $transformerFactory)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var ReadRequest $readRequest */
        $readRequest = $serializer->deserialize($content, ReadRequest::class, 'json');

        // check all properties defined
        $errors = $validator->validate($readRequest);
        if (count($errors) > 0) {
            return $this->fail(static::REQUEST_VALIDATION_FAILED);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($readRequest);
        if (null === $constructionManager) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
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
     * @Route("/file/download", name="api_file_download")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     *
     * @throws ORMException
     *
     * @return Response
     */
    public function fileDownloadAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var DownloadFileRequest $downloadFileRequest */
        $downloadFileRequest = $serializer->deserialize($content, DownloadFileRequest::class, 'json');

        // check all properties defined
        $errors = $validator->validate($downloadFileRequest);
        if (count($errors) > 0) {
            return $this->fail(static::REQUEST_VALIDATION_FAILED);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($downloadFileRequest);
        if (null === $constructionManager) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        $downloadFile = function ($repository, $objectMeta, $verifyAccess, $accessFilePath) {
            /** @var ObjectMeta $objectMeta */
            /** @var EntityRepository $repository */
            $entity = $repository->find($objectMeta->getId());

            /** @var TimeTrait $entity */
            if (null === $entity) {
                return $this->fail(static::ENTITY_NO_DOWNLOADABLE_FILE);
            }

            if (!$verifyAccess($entity)) {
                return $this->fail(static::ENTITY_ACCESS_DENIED);
            }

            if ($entity->getLastChangedAt()->format('c') !== $objectMeta->getLastChangeTime()) {
                return $this->fail(static::INVALID_TIMESTAMP);
            }

            $filePath = $accessFilePath($entity);
            if (null === $filePath) {
                return $this->fail(static::ENTITY_ACCESS_DENIED);
            }

            $filePath = $this->getParameter('PUBLIC_DIR') . '/' . $filePath;
            if (!file_exists($filePath)) {
                return $this->fail(static::ENTITY_FILE_NOT_FOUND);
            }

            return $this->file($filePath);
        };

        //get file
        if (null !== $downloadFileRequest->getMap()) {
            return $downloadFile(
                $this->getDoctrine()->getRepository(Map::class),
                $downloadFileRequest->getMap(),
                function ($entity) use ($constructionManager) {
                    /* @var Map $entity */
                    return $entity->getConstructionSite()->getConstructionManagers()->contains($constructionManager);
                },
                function ($entity) {
                    /* @var Map $entity */
                    return $entity->getFilePath();
                }
            );
        } elseif (null !== $downloadFileRequest->getIssue()) {
            return $downloadFile(
                $this->getDoctrine()->getRepository(Issue::class),
                $downloadFileRequest->getIssue(),
                function ($entity) use ($constructionManager) {
                    /* @var Issue $entity */
                    return $entity->getMap()->getConstructionSite()->getConstructionManagers()->contains($constructionManager);
                },
                function ($entity) {
                    /* @var Issue $entity */
                    return $entity->getImageFilePath();
                }
            );
        } elseif (null !== $downloadFileRequest->getBuilding()) {
            return $downloadFile(
                $this->getDoctrine()->getRepository(ConstructionSite::class),
                $downloadFileRequest->getBuilding(),
                function ($entity) use ($constructionManager) {
                    /* @var ConstructionSite $entity */
                    return $entity->getConstructionManagers()->contains($constructionManager);
                },
                function ($entity) {
                    /* @var ConstructionSite $entity */
                    return $entity->getImageFilePath();
                }
            );
        }

        //construct answer
        return $this->fail(static::REQUEST_VALIDATION_FAILED);
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

    /**
     * @Route("/issue/create", name="api_issue_create")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueCreateAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueModifyRequest($request, $serializer, $validator, $issueTransformer, 'create');
    }

    /**
     * @Route("/issue/update", name="api_issue_update")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueUpdateAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueModifyRequest($request, $serializer, $validator, $issueTransformer, 'update');
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     * @param $mode
     *
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return JsonResponse|Response
     */
    private function processIssueModifyRequest(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer, $mode)
    {
        //check if empty request, ensure multipart correctly handled
        $content = $request->request->get('message');
        if (!$content) {
            $content = $request->getContent();
        }
        if (!($content)) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var IssueModifyRequest $issueModifyRequest */
        $issueModifyRequest = $serializer->deserialize($content, IssueModifyRequest::class, 'json');

        // check all properties defined
        $errors = $validator->validate($issueModifyRequest);
        if (count($errors) > 0) {
            return $this->fail(static::REQUEST_VALIDATION_FAILED);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($issueModifyRequest);
        if (null === $constructionManager) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        $entity = null;
        $newImageExpected = '' !== $issueModifyRequest->getIssue()->getImageFilename();
        if ('create' === $mode) {
            //ensure GUID not in use already
            $existing = $this->getDoctrine()->getRepository(Issue::class)->find($issueModifyRequest->getIssue()->getMeta()->getId());
            if (null !== $existing) {
                return $this->fail(static::ISSUE_GUID_ALREADY_IN_USE);
            }
            $entity = new Issue();
        } elseif ('update' === $mode) {
            //ensure issue exists
            $existing = $this->getDoctrine()->getRepository(Issue::class)->find($issueModifyRequest->getIssue()->getMeta()->getId());
            if (null === $existing) {
                return $this->fail(static::ISSUE_NOT_FOUND);
            }
            $entity = $existing;
            $newImageExpected &= $issueModifyRequest->getIssue()->getImageFilename() !== $existing->getImageFilename();
        } else {
            throw new \InvalidArgumentException('mode must be create or update');
        }

        //transform to entity
        $issue = $issueTransformer->fromApi($issueModifyRequest->getIssue(), $entity);
        $issue->setUploadBy($constructionManager);
        $issue->setUploadedAt(new \DateTime());

        //get map & check access
        if (null !== $issueModifyRequest->getIssue()->getMap()) {
            /** @var Map $map */
            $map = $this->getDoctrine()->getRepository(Map::class)->findOneBy(['id' => $issueModifyRequest->getIssue()->getMap()]);
            if (null === $map) {
                return $this->fail(static::MAP_NOT_FOUND);
            }
            if (!$map->getConstructionSite()->getConstructionManagers()->contains($constructionManager)) {
                return $this->fail(static::MAP_ACCESS_DENIED);
            }
            $issue->setMap($map);
        }

        //get craftsmen & check access
        if (null !== $issueModifyRequest->getIssue()->getCraftsman()) {
            /** @var Craftsman $craftsman */
            $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['id' => $issueModifyRequest->getIssue()->getCraftsman()]);
            if (null === $craftsman) {
                return $this->fail(static::CRAFTSMAN_NOT_FOUND);
            }
            if (!$craftsman->getConstructionSite()->getConstructionManagers()->contains($constructionManager)) {
                return $this->fail(static::CRAFTSMAN_ACCESS_DENIED);
            }
            $issue->setCraftsman($craftsman);
        }

        //ensure craftsman & map on same construction site
        if (null !== $issue->getMap() && null !== $issue->getCraftsman() &&
            $issue->getMap()->getConstructionSite()->getId() !== $issue->getCraftsman()->getConstructionSite()->getId()) {
            return $this->fail(static::MAP_CRAFTSMAN_NOT_ON_SAME_CONSTRUCTION_SITE);
        }

        //handle file uploads
        if ($newImageExpected && 1 !== count($request->files->all())) {
            return $this->fail(static::ISSUE_NO_FILE_TO_UPLOAD);
        }
        if (!$newImageExpected && 0 !== count($request->files->all())) {
            return $this->fail(static::ISSUE_NO_FILE_UPLOAD_EXPECTED);
        }
        foreach ($request->files->all() as $key => $file) {
            /** @var UploadedFile $file */
            $targetFolder = $this->getParameter('PUBLIC_DIR') . '/' . dirname($issue->getImageFilePath());
            if (!file_exists($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }
            if (!$file->move($targetFolder, $issue->getImageFilename())) {
                return $this->fail(static::ISSUE_FILE_UPLOAD_FAILED);
            }
        }

        if ('create' === $mode) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            //deactivate guid generator so we can use the one the client has sent us
            $metadata = $em->getClassMetadata(get_class($issue));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
            $issue->setId($issueModifyRequest->getIssue()->getMeta()->getId());

            //persist to db
            $em->persist($issue);
            $em->flush();
        } else {
            $this->fastSave($issue);
        }

        //construct answer
        return $this->success(new IssueData($issueTransformer->toApi($issue)));
    }

    /**
     * @Route("/issue/delete", name="api_issue_delete")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueDeleteAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request,
            $serializer,
            $validator,
            $issueTransformer,
            function ($issue) {
                /** @var Issue $issue */
                if (null === $issue->getRegisteredAt()) {
                    $this->fastRemove($issue);

                    return $this->success(new EmptyData());
                }

                return $this->fail(static::ISSUE_ACTION_NOT_ALLOWED);
            }
        );
    }

    /**
     * @Route("/issue/mark", name="api_issue_mark")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueMarkAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request,
            $serializer,
            $validator,
            $issueTransformer,
            function ($issue) {
                /* @var Issue $issue */
                $issue->setIsMarked(!$issue->getIsMarked());
                $this->fastSave($issue);

                return true;
            }
        );
    }

    /**
     * @Route("/issue/review", name="api_issue_review")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueReviewAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request,
            $serializer,
            $validator,
            $issueTransformer,
            function ($issue, $constructionManager) {
                /** @var Issue $issue */
                /* @var ConstructionManager $constructionManager */
                if (null !== $issue->getRegisteredAt() && null === $issue->getReviewedAt()) {
                    $issue->setReviewedAt(new \DateTime());
                    $issue->setReviewBy($constructionManager);
                    $this->fastSave($issue);

                    return true;
                }

                return $this->fail(static::ISSUE_ACTION_NOT_ALLOWED);
            }
        );
    }

    /**
     * @Route("/issue/revert", name="api_issue_revert")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueRevertAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request,
            $serializer,
            $validator,
            $issueTransformer,
            function ($issue) {
                /** @var Issue $issue */
                if (null !== $issue->getRegisteredAt()) {
                    if (null !== $issue->getReviewedAt()) {
                        $issue->setReviewedAt(null);
                        $issue->setReviewBy(null);
                    } elseif (null !== $issue->getRespondedAt()) {
                        $issue->setRespondedAt(null);
                        $issue->setResponseBy(null);
                    } else {
                        return $this->fail(static::ISSUE_ACTION_NOT_ALLOWED);
                    }
                    $this->fastSave($issue);

                    return true;
                }

                return $this->fail(static::ISSUE_ACTION_NOT_ALLOWED);
            }
        );
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     * @param $action
     *
     * @throws ORMException
     *
     * @return JsonResponse|Response
     */
    private function processIssueActionRequest(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer, $action)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var IssueActionRequest $issueActionRequest */
        $issueActionRequest = $serializer->deserialize($content, IssueActionRequest::class, 'json');

        // check all properties defined
        $errors = $validator->validate($issueActionRequest);
        if (count($errors) > 0) {
            return $this->fail(static::REQUEST_VALIDATION_FAILED);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($issueActionRequest);
        if (null === $constructionManager) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        //get issue
        /** @var Issue $issue */
        $issue = $this->getDoctrine()->getRepository(Issue::class)->find($issueActionRequest->getIssueID());
        if (null === $issue) {
            return $this->fail(static::ISSUE_NOT_FOUND);
        }
        //ensure we are allowed to access this issue
        if (!$issue->getMap()->getConstructionSite()->getConstructionManagers()->contains($constructionManager)) {
            return $this->fail(static::ISSUE_ACCESS_DENIED);
        }

        //execute action
        $response = $action($issue, $constructionManager);
        if ($response instanceof Response) {
            return $response;
        }

        //construct answer
        return $this->success(new IssueData($issueTransformer->toApi($issue)));
    }

    /**
     * if request errored (server error).
     *
     * @param string $message
     *
     * @return Response
     */
    protected function error(string $message)
    {
        $logger = $this->get('logger');
        $request = $this->get('request_stack')->getCurrentRequest();
        $logger->error('Api error ' . ': ' . $message . ' for ' . $request->getContent());

        return $this->json(new ErrorResponse($message, $this->errorMessageToStatusCode($message)), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * if request failed (client error).
     *
     * @param string $message
     *
     * @return Response
     */
    protected function fail(string $message)
    {
        $logger = $this->get('logger');
        $request = $this->get('request_stack')->getCurrentRequest();
        $logger->error('Api fail ' . ': ' . $message . ' for ' . $request->getContent());

        return $this->json(new FailResponse($message, $this->errorMessageToStatusCode($message)), Response::HTTP_BAD_REQUEST);
    }

    /**
     * if request was successful.
     *
     * @param $data
     *
     * @return JsonResponse
     */
    protected function success($data)
    {
        return $this->json(new SuccessfulResponse($data));
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @final
     *
     * @param $data
     * @param int $status
     * @param array $headers
     * @param array $context
     *
     * @return JsonResponse
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        $serializer = $this->get('serializer');

        $json = $serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS | JSON_UNESCAPED_UNICODE,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}
