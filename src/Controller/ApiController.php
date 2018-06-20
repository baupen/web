<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use App\Api\Entity\ObjectMeta;
use App\Api\Request\IssueActionRequest;
use App\Api\Request\IssueModifyRequest;
use App\Api\Request\LoginRequest;
use App\Api\Request\ReadRequest;
use App\Api\Response\Data\EmptyData;
use App\Api\Response\Data\IssueData;
use App\Api\Response\Data\LoginData;
use App\Api\Response\Data\ReadData;
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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use MongoDB\Driver\ReadConcern;
use PhpCsFixer\Tokenizer\TransformerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
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
    const EMPTY_REQUEST = "request empty";
    const INVALID_REQUEST = "invalid request";
    const UNKNOWN_USERNAME = "unknown username";
    const WRONG_PASSWORD = "wrong password";
    const GUID_ALREADY_IN_USE = "guid already in use";
    const GUID_NOT_FOUND = "guid not found";
    const INVALID_ENTITY = "invalid entity";
    const INVALID_ACTION = "invalid action";
    const AUTHENTICATION_TOKEN_INVALID = "authentication token invalid";

    /**
     * inject the translator service
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + ['translator' => TranslatorInterface::class, "logger" => LoggerInterface::class];
    }

    /**
     * @Route("/login", name="api_login")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserTransformer $userTransformer
     * @return Response
     */
    public function loginAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, UserTransformer $userTransformer)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var LoginRequest $loginRequest */
        $loginRequest = $serializer->deserialize($content, LoginRequest::class, "json");

        // check all properties defined
        $errors = $validator->validate($loginRequest);
        if (count($errors) > 0) {
            return $this->fail(static::INVALID_REQUEST);
        }

        //check username & password
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(["email" => $loginRequest->getUsername()]);
        if ($constructionManager === null) {
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
     * @param IssueTransformer $issueTransformer
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function apiAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, TransformerFactory $transformerFactory)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var ReadRequest $readRequest */
        $readRequest = $serializer->deserialize($content, ReadRequest::class, "json");

        // check all properties defined
        $errors = $validator->validate($readRequest);
        if (count($errors) > 0) {
            return $this->fail(static::INVALID_REQUEST);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($readRequest);
        if ($constructionManager === null) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        //construct read data
        $readData = new ReadData();
        if ($constructionManager->getLastChangedAt() > new \DateTime($readRequest->getUser()->getLastChangeTime())) {
            $readData->setUser($transformerFactory->getUserTransformer()->toApi($constructionManager, $readRequest->getAuthenticationToken()));
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
     * @param ObjectMeta[] $objectMetas
     * @return array
     */
    private function objectMetaToDictionary(array $objectMetas)
    {
        $res = [];
        foreach ($objectMetas as $objectMeta) {
            $res[$objectMeta["id"]] = $objectMeta["lastChangeTime"];
        }
        return $res;
    }

    /**
     * @param TransformerFactory $transformerFactory
     * @param ObjectMeta[] $objectMetas
     * @param ConstructionManager $constructionManager
     * @param ReadData $readData
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

        ### BUILDING ###
        //prepare mapping builder
        $resultSetMapping = new ResultSetMappingBuilder($incompleteManager);
        $resultSetMapping->addRootEntityFromClassMetadata(ConstructionSite::class, 's');
        $resultSetMapping->addFieldResult('s', 'id', 'id');

        //get allowed building ids
        $sql = "SELECT DISTINCT s.id FROM construction_site s INNER JOIN construction_site_construction_manager cscm ON cscm.construction_site_id = s.id
WHERE cscm.construction_manager_id = :id";
        $query = $incompleteManager->createNativeQuery($sql, $resultSetMapping);
        $query->setParameters(["id" => $constructionManager->getId()]);
        /** @var IdTrait[] $validConstructionSites */
        $validConstructionSites = $query->getResult();
        $this->filterIds($readRequest->getBuildings(), $validConstructionSites, $allValidIds, $removeIds, $knownIds);

        //set the removed/invalid buildings
        $readData->setRemovedBuildingIDs(array_keys($removeIds));

        //if no access to any buildings do an early return
        if (count($allValidIds) == 0) {
            return;
        }

        //get updated / new buildings
        $sql = 'SELECT DISTINCT s.id FROM construction_site s WHERE s.id IN ("' . implode('", "', $allValidIds) . '")';
        $parameters = [];
        $this->addUpdateUnknownConditions($parameters, $sql, $knownIds, "s");

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
                $manager->getRepository(ConstructionSite::class)->findBy(["id" => $retrieveConstructionSiteIds])
            )
        );
        $validConstructionSiteIds = $allValidIds;

        ### craftsman ###
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
        $this->addUpdateUnknownConditions($parameters, $sql, $knownIds, "c");

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
                $manager->getRepository(Craftsman::class)->findBy(["id" => $retrieveCraftsmanIds])
            )
        );

        ### maps ###
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
        $this->addUpdateUnknownConditions($parameters, $sql, $knownIds, "m");

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
                $manager->getRepository(Map::class)->findBy(["id" => $retrieveMapIds])
            )
        );
        $validMapIds = $allValidIds;


        ### issues ###
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
        $this->addUpdateUnknownConditions($parameters, $sql, $knownIds, "i");

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
                $manager->getRepository(Issue::class)->findBy(["id" => $retrieveIssueIds])
            )
        );
    }

    /**
     * @param ObjectMeta[] $requestObjectMeta the given ids
     * @param IdTrait[] $dbEntities
     * @param string[] $allValidIds contains all ids from the db
     * @param string[] $removeIds contains the invalid given (ids -> time)
     * @param string[] $knownIds contains the valid given (id -> time)
     */
    private function filterIds($requestObjectMeta, $dbEntities, &$allValidIds, &$removeIds, &$knownIds)
    {
        $allValidIds = [];
        $removeIds = $this->objectMetaToDictionary($requestObjectMeta);
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

    private function addUpdateUnknownConditions(&$parameters, &$sql, $guidTimeDictionary, $tableShort)
    {
        $sql .= ' AND (';
        //only return confirmed buildings if they are updated
        if (count($guidTimeDictionary) > 0) {
            $sql .= "(";

            //get all where id matches but last change date does not
            $whereCondition = "";
            $parameters = [];
            $counter = 0;
            foreach (array_keys($guidTimeDictionary) as $confirmedBuildingId) {
                if (strlen($whereCondition) > 0) {
                    $whereCondition .= " OR ";
                }
                $whereCondition .= '(' . $tableShort . '.id == "' . $confirmedBuildingId . '" AND ' . $tableShort . '.last_changed_at > :time_' . $counter . ')';
                $parameters["time_" . $counter] = $guidTimeDictionary[$confirmedBuildingId];

                $counter++;
            }
            $sql .= $whereCondition;
            $sql .= ") OR ";
        }

        //return buildings unknown to the requester
        if (count($guidTimeDictionary) > 0) {
            $sql .= $tableShort . '.id NOT IN ("' . implode('", "', array_keys($guidTimeDictionary)) . '")';
        } else {
            //allow all
            $sql .= "1 = 1";
        }

        $sql .= ")";
    }

    /**
     * @Route("/issue/create", name="api_issue_create")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function issueCreateAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueModifyRequest($request, $serializer, $validator, $issueTransformer, "create");
    }

    /**
     * @Route("/issue/update", name="api_issue_update")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function issueUpdateAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueModifyRequest($request, $serializer, $validator, $issueTransformer, "update");
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     * @return JsonResponse|Response
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function processIssueModifyRequest(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer, $mode)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var IssueModifyRequest $issueModifyRequest */
        $issueModifyRequest = $serializer->deserialize($content, IssueModifyRequest::class, "json");

        // check all properties defined
        $errors = $validator->validate($issueModifyRequest);
        if (count($errors) > 0) {
            return $this->fail(static::INVALID_REQUEST);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($issueModifyRequest);
        if ($constructionManager === null) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        $entity = null;
        if ($mode == "create") {
            //ensure GUID not in use already
            $existing = $this->getDoctrine()->getRepository(Issue::class)->find($issueModifyRequest->getIssue()->getMeta()->getId());
            if ($existing != null) {
                return $this->fail(static::GUID_ALREADY_IN_USE);
            }
            $entity = new Issue();
        } else if ($mode == "update") {
            //ensure issue exists
            $existing = $this->getDoctrine()->getRepository(Issue::class)->find($issueModifyRequest->getIssue()->getMeta()->getId());
            if ($existing == null) {
                return $this->fail(static::GUID_NOT_FOUND);
            }
            $entity = $existing;
        } else {
            throw new \InvalidArgumentException("mode must be create or update");
        }

        //transform to entity & persist
        $issue = $issueTransformer->fromApi($issueModifyRequest->getIssue(), $entity);
        if ($issue == null) {
            return $this->fail(static::INVALID_ENTITY);
        }
        $issue->setUploadBy($constructionManager);
        $issue->setUploadedAt(new \DateTime());

        if ($mode == "create") {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $metadata = $em->getClassMetadata(get_class($issue));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
            $issue->setId($issueModifyRequest->getIssue()->getMeta()->getId());
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
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function issueDeleteAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest($request, $serializer, $validator, $issueTransformer,
            function ($issue) {
                /** @var Issue $issue */
                if ($issue->getRegisteredAt() == null) {
                    $this->fastRemove($issue);
                    return $this->success(new EmptyData());
                } else {
                    return $this->fail(static::INVALID_ACTION);
                }
            });
    }

    /**
     * @Route("/issue/mark", name="api_issue_mark")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function issueMarkAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest($request, $serializer, $validator, $issueTransformer,
            function ($issue) {
                /** @var Issue $issue */
                $issue->setIsMarked(!$issue->getIsMarked());
                $this->fastSave($issue);
                return true;
            });
    }

    /**
     * @Route("/issue/review", name="api_issue_review")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function issueReviewAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest($request, $serializer, $validator, $issueTransformer,
            function ($issue, $constructionManager) {
                /** @var Issue $issue */
                /** @var ConstructionManager $constructionManager */
                if ($issue->getRegisteredAt() != null && $issue->getReviewedAt() == null) {
                    $issue->setReviewedAt(new \DateTime());
                    $issue->setReviewBy($constructionManager);
                    $this->fastSave($issue);
                    return true;
                } else {
                    return $this->fail(static::INVALID_ACTION);
                }
            });
    }

    /**
     * @Route("/issue/revert", name="api_issue_revert")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function issueRevertAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest($request, $serializer, $validator, $issueTransformer,
            function ($issue, $constructionManager) {
                /** @var Issue $issue */
                /** @var ConstructionManager $constructionManager */
                if ($issue->getRegisteredAt() != null && $issue->getRespondedAt() != null || ) {
                    if ($issue->getReviewedAt() != null) {
                        $issue->setReviewedAt(null);
                        $issue->setReviewBy(null);
                    } else if ($issue->getRespondedAt() != null) {
                        $issue->setRespondedAt(null);
                        $issue->setResponseBy(null);
                    } else {
                        return $this->fail(static::INVALID_ACTION);
                    }
                    $this->fastSave($issue);
                    return true;
                } else {
                    return $this->fail(static::INVALID_ACTION);
                }
            });
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param IssueTransformer $issueTransformer
     * @return JsonResponse|Response
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function processIssueActionRequest(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer, $action)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var IssueActionRequest $issueActionRequest */
        $issueActionRequest = $serializer->deserialize($content, IssueActionRequest::class, "json");

        // check all properties defined
        $errors = $validator->validate($issueActionRequest);
        if (count($errors) > 0) {
            return $this->fail(static::INVALID_REQUEST);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($issueActionRequest);
        if ($constructionManager === null) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        //get issue
        $issue = $this->getDoctrine()->getRepository(Issue::class)->find($issueActionRequest->getIssueID());
        if ($issue == null) {
            return $this->fail(static::GUID_NOT_FOUND);
        }

        //execute action
        $response = $action($issue, $constructionManager);
        if ($response instanceof Response) {
            return $response;
        }

        //construct answer
        return $this->success(new IssueData($issueTransformer->toApi($issue)));
    }


//    public function fileUploadAction(Request $request)
//    {
//        foreach ($request->files->all() as $key => $file) {
//            /** @var UploadedFile $file */
//            if (!$file->move($this->getParameter("PUBLIC_DIR") . "/upload", $file->getClientOriginalName())) {
//                return $this->failed(ApiStatus::INVALID_FILE);
//            }
//        }
//
//        return $this->file($this->getParameter("PUBLIC_DIR") . "/upload/" . $downloadFileRequest->getFileName());
//    }

    /**
     * if request failed
     *
     * @param string $message
     * @param int $code
     * @return Response
     */
    protected function fail(string $message)
    {
        $logger = $this->get("logger");
        $request = $this->get("request_stack")->getCurrentRequest();
        $logger->error("Api fail " . ": " . $message . " for " . $request->getContent());
        $code = Response::HTTP_OK;
        switch ($message) {
            case static::INVALID_REQUEST:
                $code = Response::HTTP_BAD_REQUEST;
                break;
            case static::AUTHENTICATION_TOKEN_INVALID:
                $code = Response::HTTP_UNAUTHORIZED;
                break;
        }
        return $this->json(new FailResponse($message), $code);
    }

    /**
     * if request was successful
     *
     * @param $data
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
     * @param $data
     * @param int $status
     * @param array $headers
     * @param array $context
     * @return JsonResponse
     */
    protected function json($data, int $status = 200, array $headers = array(), array $context = array()): JsonResponse
    {
        $serializer = $this->get("serializer");

        $json = $serializer->serialize($data, 'json', array_merge(array(
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ), $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}
