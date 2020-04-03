<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api\External;

use App\Api\External\Entity\Issue;
use App\Api\External\Entity\IssuePosition;
use App\Api\External\Entity\IssueStatus;
use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Entity\Point;
use App\Api\External\Request\ReadRequest;
use App\Controller\Api\External\IssueController;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\External\Base\ApiController;
use DateTime;
use Exception;

class LoadTest extends ApiController
{
    /**
     * tests the create issue method.
     *
     * @throws Exception
     */
    public function testLoad1000()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get('serializer');
        $doRequest = function (Issue $issue) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, 'json') . '}';
            $client->request(
                'POST',
                '/api/external/issue/create',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);
        $map = $this->getMapWithFile($serverData->getMaps());

        $issue = new Issue();
        $issue->setWasAddedWithClient(true);
        $issue->setIsMarked(true);
        $issue->setDescription('description');
        $issue->setMap($map->getMeta()->getId());

        $issue->setStatus(new IssueStatus());

        $meta = new ObjectMeta();
        $meta->setId($this->getNewGuid());
        $meta->setLastChangeTime((new DateTime())->format('c'));
        $issue->setMeta($meta);

        $issuePosition = new IssuePosition();
        $point = new Point();
        $point->setX(0.3);
        $point->setY(0.4);
        $issuePosition->setPoint($point);
        $issuePosition->setZoomScale(0.5);
        $issuePosition->setMapFileID($map->getFile()->getId());
        $issue->setPosition($issuePosition);

        $createdIssues = 1001;
        for ($i = 0; $i < $createdIssues; $i++) {
            $meta->setId($this->getNewGuid());
            $response = $doRequest($issue);
            $this->checkResponse($response, ApiStatus::SUCCESS);
        }

        $doReadRequest = function (ReadRequest $readRequest) use ($client, $serializer) {
            $json = $serializer->serialize($readRequest, 'json');
            $client->request(
                'POST',
                '/api/external/read',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        //## update none
        $readRequest = new ReadRequest();
        $readRequest->setAuthenticationToken($user->authenticationToken);
        $userMeta = new ObjectMeta();
        $userMeta->setId($user->meta->id);
        $userMeta->setLastChangeTime($user->meta->lastChangeTime);
        $readRequest->setUser($userMeta);
        $readRequest->setConstructionSites([]);
        $readRequest->setCraftsmen([]);
        $readRequest->setIssues([]);
        $readRequest->setMaps([]);

        $response = $doReadRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($readResponse->data);
        $this->assertNull($readResponse->data->changedUser);
        $this->assertGreaterThanOrEqual($createdIssues, count($readResponse->data->changedIssues));
    }

    /**
     * tests the create issue method.
     *
     * @throws Exception
     */
    public function testUpdateIssue()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get('serializer');
        $doRequest = function (Issue $issue) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, 'json') . '}';
            $client->request(
                'POST',
                '/api/external/issue/update',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);

        /** @var Issue $issue */
        $issue = $serverData->getIssues()[0];
        $issue->setWasAddedWithClient(false);
        $issue->setIsMarked(false);
        $issue->setDescription('description 2');

        $issue->setStatus(new IssueStatus());

        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response has issue
        $this->assertNotNull($issueResponse->data);
        $this->assertNotNull($issueResponse->data->issue);
        $checkIssue = $issueResponse->data->issue;
        //fully check issue
        $this->verifyIssue($checkIssue, $issue);

        //check with non-existing
        $issue->getMeta()->setId($this->getNewGuid());
        $response = $doRequest($issue);
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_NOT_FOUND);
    }

    /**
     * tests upload/download functionality.
     *
     * @throws Exception
     */
    public function testIssueActions()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $doRequest = function ($issueId, $action) use ($client, $user) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issueID":"' . $issueId . '"}';
            $client->request(
                'POST',
                '/api/external/issue/' . $action,
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);
        $issue = $serverData->getIssues()[0];

        $response = $doRequest($issue->getMeta()->getId(), 'mark');
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response issue updated
        $issue->setIsMarked(!$issue->getIsMarked());
        $this->verifyIssue($issueResponse->data->issue, $issue);

        /* @var Issue[] $newIssues */
        /* @var Issue[] $registeredIssues */
        /* @var Issue[] $respondedIssues */
        /* @var Issue[] $reviewedIssues */
        $this->categorizeIssues($serverData->getIssues(), $newIssues, $registeredIssues, $respondedIssues, $reviewedIssues);

        //delete
        $response = $doRequest($newIssues[0]->getMeta()->getId(), 'delete');
        $this->checkResponse($response, ApiStatus::SUCCESS);
        $response = $doRequest($newIssues[0]->getMeta()->getId(), 'delete');
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_NOT_FOUND);

        //review registered
        $response = $doRequest($registeredIssues[0]->getMeta()->getId(), 'review');
        $this->checkResponse($response, ApiStatus::SUCCESS);
        $response = $doRequest($registeredIssues[0]->getMeta()->getId(), 'review');
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_ACTION_NOT_ALLOWED);

        //review responded
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), 'review');
        $this->checkResponse($response, ApiStatus::SUCCESS);
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), 'review');
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_ACTION_NOT_ALLOWED);

        //revert reviewed
        $response = $doRequest($reviewedIssues[0]->getMeta()->getId(), 'revert');
        $this->checkResponse($response, ApiStatus::SUCCESS);

        //revert responded
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), 'revert');
        $this->checkResponse($response, ApiStatus::SUCCESS);
        //revert twice because of earlier actions
        $doRequest($respondedIssues[0]->getMeta()->getId(), 'revert');
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), 'revert');
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_ACTION_NOT_ALLOWED);
    }
}
