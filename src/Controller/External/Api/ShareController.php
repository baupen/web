<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External\Api;

use App\Api\Request\Share\IssueRequest;
use App\Api\Response\Data\MapsData;
use App\Api\Response\Data\ProcessingEntitiesData;
use App\Api\Transformer\Share\IssueTransformer;
use App\Api\Transformer\Share\MapTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/share")
 */
class ShareController extends ApiController
{
    const INVALID_IDENTIFIER = 'invalid identifier';
    const INVALID_ISSUE = 'invalid issue';
    const TIMEOUT_EXCEEDED = "timeout exceeded; can't change response anymore";

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
     * @param $identifier
     * @param $craftsman
     * @param $errorResponse
     *
     * @return bool
     */
    private function parseIdentifierRequest($identifier, &$craftsman, &$errorResponse)
    {
        /** @var Craftsman $craftsman */
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['emailIdentifier' => $identifier]);
        if ($craftsman === null) {
            $errorResponse = $this->fail(self::INVALID_IDENTIFIER);

            return false;
        }

        return true;
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
    private function parseIssueRequest(Request $request, $identifier, Craftsman $craftsman, &$issue, &$errorResponse)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($identifier, $craftsman, $errorResponse)) {
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

    /**
     * @Route("/c/{identifier}/maps/list", name="external_api_share_craftsman_maps_list", methods={"POST"})
     *
     * @param $identifier
     * @param MapTransformer $mapTransformer
     *
     * @return Response
     */
    public function craftsmanMapsListAction($identifier, MapTransformer $mapTransformer, IssueTransformer $issueTransformer)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($identifier, $craftsman, $errorResponse)) {
            return $errorResponse;
        }

        //get all relevant issues
        $filter = new Filter();
        $filter->setConstructionSite($craftsman->getConstructionSite()->getId());
        $filter->setCraftsmen([$craftsman->getId()]);
        $filter->setRespondedStatus(false);
        $filter->setRegistrationStatus(true);
        $issues = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);

        /** @var Map[] $maps */
        $maps = [];
        /** @var Issue[][] $issuesPerMap */
        $issuesPerMap = [];
        foreach ($issues as $issue) {
            $maps[$issue->getMap()->getId()] = $issue;
            $issuesPerMap[$issue->getMap()->getId()][] = $issue;
        }

        //convert to api format
        $apiMaps = [];
        foreach ($maps as $key => $map) {
            $apiMap = $mapTransformer->toApi($map);
            $apiMap->setImageFilePath($this->generateUrl('external_image_map_craftsman', ['map' => $map->getId(), 'identifier' => $craftsman->getEmailIdentifier()]));
            $apiMap->setIssues($issueTransformer->toApiMultiple($issuesPerMap[$key]));
            $apiMaps[] = $apiMap;
        }

        //output
        $data = new MapsData();
        $data->setMaps($apiMaps);

        return $this->success($data);
    }

    /**
     * @Route("/c/{identifier}/issue/respond", name="external_api_share_craftsman_issue_respond", methods={"POST"})
     *
     * @param Request $request
     * @param $identifier
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

        $data = new ProcessingEntitiesData();

        if ($issue->getRespondedAt() === null) {
            $issue->setRespondedAt(new \DateTime());
            $issue->setResponseBy($craftsman);
            $this->fastSave($issue);
            $data->addSuccessfulId($issue->getId());
        } else {
            $data->addSkippedId($issue->getId());
        }

        return $this->success($data);
    }

    /**
     * @Route("/c/{identifier}/issue/remove_response", name="external_api_share_craftsman_issue_remove_response", methods={"POST"})
     *
     * @param Request $request
     * @param $identifier
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

        $data = new ProcessingEntitiesData();
        if ($issue->getRespondedAt() === null) {
            $data->addSkippedId($issue->getId());
        } elseif ($issue->getRespondedAt() < new \DateTime('now -5 hours')) {
            return $this->fail(self::TIMEOUT_EXCEEDED);
        } else {
            $issue->setRespondedAt(null);
            $issue->setResponseBy(null);
            $this->fastSave($issue);
            $data->addSuccessfulId($issue->getId());
        }

        return $this->success($data);
    }
}
