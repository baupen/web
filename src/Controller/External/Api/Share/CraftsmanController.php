<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External\Api\Share;

use App\Api\Request\Share\Craftsman\IssueRequest;
use App\Api\Response\Data\CraftsmanData;
use App\Api\Response\Data\MapsData;
use App\Api\Response\Data\ProcessingEntitiesData;
use App\Api\Transformer\Share\Craftsman\CraftsmanTransformer;
use App\Api\Transformer\Share\Craftsman\MapTransformer;
use App\Controller\Api\Base\ApiController;
use App\Controller\External\Traits\CraftsmanAuthenticationTrait;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Map;
use App\Helper\IssueHelper;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/c/{identifier}")
 */
class CraftsmanController extends ApiController
{
    use CraftsmanAuthenticationTrait;

    const INVALID_IDENTIFIER = 'invalid identifier';
    const INVALID_ISSUE = 'invalid issue';
    const TIMEOUT_EXCEEDED = "timeout exceeded; can't change response anymore";

    /**
     * @Route("/read", name="external_api_share_craftsman_read", methods={"GET"})
     *
     * @param Request $request
     * @param $identifier
     * @param CraftsmanTransformer $craftsmanTransformer
     *
     * @return Response
     */
    public function readAction(Request $request, $identifier, CraftsmanTransformer $craftsmanTransformer)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $craftsman)) {
            return $this->fail(self::INVALID_IDENTIFIER);
        }

        $data = new CraftsmanData();
        $data->setCraftsman($craftsmanTransformer->toApi($craftsman, $identifier, $this->checkWriteAuthenticationToken($request, $craftsman)));

        return $this->success($data);
    }

    /**
     * @Route("/maps/list", name="external_api_share_craftsman_maps_list", methods={"GET"})
     *
     * @param $identifier
     * @param MapTransformer $mapTransformer
     *
     * @throws Exception
     *
     * @return Response
     */
    public function craftsmanMapsListAction($identifier, MapTransformer $mapTransformer)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $craftsman)) {
            return $this->fail(self::INVALID_IDENTIFIER);
        }

        //get all relevant issues
        $filter = self::createCraftsmanFilter($craftsman);
        $issues = $this->getDoctrine()->getRepository(Issue::class)->findByFilter($filter);

        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);

        //convert to api format
        $apiMaps = [];
        foreach ($orderedMaps as $key => $map) {
            $apiMap = $mapTransformer->toApi($map, $craftsman->getEmailIdentifier(), $issuesPerMap[$key]);
            $apiMaps[] = $apiMap;
        }

        //output
        $data = new MapsData();
        $data->setMaps($apiMaps);

        return $this->success($data);
    }

    /**
     * @Route("/issue/respond", name="external_api_share_craftsman_issue_respond", methods={"POST"})
     *
     * @param Request $request
     * @param $identifier
     *
     * @throws Exception
     *
     * @return Response
     */
    public function issueRespondAction(Request $request, $identifier)
    {
        /** @var Craftsman $craftsman */
        /** @var Issue $issue */
        if (!$this->parseIssueRequest($request, $identifier, $craftsman, $issue, $errorResponse)) {
            return $errorResponse;
        }

        if (!$this->checkWriteAuthenticationToken($request, $craftsman)) {
            throw new AccessDeniedHttpException();
        }

        $data = new ProcessingEntitiesData();
        if ($issue->getRespondedAt() === null) {
            $issue->setRespondedAt(new DateTime());
            $issue->setResponseBy($craftsman);
            $this->fastSave($issue);
            $data->addSuccessfulId($issue->getId());
        } else {
            $data->addSkippedId($issue->getId());
        }

        return $this->success($data);
    }

    /**
     * @Route("/issue/remove_response", name="external_api_share_craftsman_issue_remove_response", methods={"POST"})
     *
     * @param Request $request
     * @param $identifier
     *
     * @throws Exception
     *
     * @return Response
     */
    public function issueRemoveResponseAction(Request $request, $identifier)
    {
        /** @var Craftsman $craftsman */
        /** @var Issue $issue */
        if (!$this->parseIssueRequest($request, $identifier, $craftsman, $issue, $errorResponse)) {
            return $errorResponse;
        }

        if (!$this->checkWriteAuthenticationToken($request, $craftsman)) {
            throw new AccessDeniedHttpException();
        }

        $data = new ProcessingEntitiesData();
        if ($issue->getRespondedAt() === null) {
            $data->addSkippedId($issue->getId());
        } elseif ($issue->getRespondedAt() < new DateTime('now -5 hours')) {
            return $this->fail(self::TIMEOUT_EXCEEDED);
        } else {
            $issue->setRespondedAt(null);
            $issue->setResponseBy(null);
            $this->fastSave($issue);
            $data->addSuccessfulId($issue->getId());
        }

        return $this->success($data);
    }

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        return parent::errorMessageToStatusCode($message);
    }

    /**
     * @param Request $request
     * @param $identifier
     * @param Craftsman $craftsman
     * @param $issue
     * @param $errorResponse
     *
     * @return bool
     */
    private function parseIssueRequest(Request $request, $identifier, &$craftsman, &$issue, &$errorResponse)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $craftsman)) {
            $errorResponse = $this->fail(self::INVALID_IDENTIFIER);

            return false;
        }

        /** @var IssueRequest $parsedRequest */
        if (!parent::parseRequest($request, IssueRequest::class, $parsedRequest, $errorResponse)) {
            return false;
        }

        $issue = $this->getDoctrine()->getRepository(Issue::class)->find($parsedRequest->getIssueId());
        if ($issue === null || $issue->getCraftsman() !== $craftsman) {
            $errorResponse = $this->fail(self::INVALID_ISSUE);

            return false;
        }

        return true;
    }
}
