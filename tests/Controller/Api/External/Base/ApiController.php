<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:11 PM
 */

namespace App\Tests\Controller\Api\External\Base;


use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\AbstractApiController;
use App\Tests\Controller\Base\FixturesTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Api\External\Entity\Base\BaseEntity;
use App\Api\External\Entity\Building;
use App\Api\External\Entity\Craftsman;
use App\Api\External\Entity\Issue;
use App\Api\External\Entity\IssuePosition;
use App\Api\External\Entity\IssueStatus;
use App\Api\External\Entity\Map;
use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\ReadRequest;
use App\Tests\Controller\ServerData;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Client;

class ApiController extends AbstractApiController
{
    /**
     * gets an authenticated user.
     *
     * @param Client $client
     *
     * @return \stdClass
     */
    protected function getAuthenticatedUser(Client $client)
    {
        $client->request(
            'POST',
            '/api/external/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"username":"f@mangel.io", "passwordHash":"' . hash('sha256', 'asdf') . '"}'
        );

        $json = $client->getResponse()->getContent();
        $response = json_decode($json);

        return $response->data->user;
    }

    /**
     * get the state of the server.
     *
     * @param Client $client
     * @param $authenticatedUser
     *
     * @return ServerData
     */
    protected function getServerEntities(Client $client, $authenticatedUser)
    {
        $serializer = $client->getContainer()->get('serializer');
        $doRequest = function (ReadRequest $readRequest) use ($client, $serializer) {
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

        // update all
        $readRequest = new ReadRequest();
        $readRequest->setAuthenticationToken($authenticatedUser->authenticationToken);

        $userMeta = new ObjectMeta();
        $userMeta->setId($authenticatedUser->meta->id);
        $userMeta->setLastChangeTime((new \DateTime())->setTimestamp(0)->format('c'));
        $readRequest->setUser($userMeta);

        $readRequest->setBuildings([]);
        $readRequest->setCraftsmen([]);
        $readRequest->setIssues([]);
        $readRequest->setMaps([]);

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($readResponse->data);
        $this->assertNotNull($readResponse->data->changedUser);
        $this->assertNotNull($readResponse->data->changedBuildings);
        $this->assertTrue(count($readResponse->data->changedBuildings) > 0);
        $this->assertTrue(count($readResponse->data->changedCraftsmen) > 0);
        $this->assertTrue(count($readResponse->data->changedMaps) > 0);
        $this->assertTrue(count($readResponse->data->changedIssues) > 0);

        $buildings = [];
        foreach ($readResponse->data->changedBuildings as $stdClass) {
            $buildings[] = $serializer->deserialize(json_encode($stdClass), Building::class, 'json');
        }
        $craftsmen = [];
        foreach ($readResponse->data->changedCraftsmen as $stdClass) {
            $craftsmen[] = $serializer->deserialize(json_encode($stdClass), Craftsman::class, 'json');
        }
        $maps = [];
        foreach ($readResponse->data->changedMaps as $stdClass) {
            $maps[] = $serializer->deserialize(json_encode($stdClass), Map::class, 'json');
        }
        $issues = [];
        foreach ($readResponse->data->changedIssues as $stdClass) {
            $issues[] = $serializer->deserialize(json_encode($stdClass), Issue::class, 'json');
        }

        $this->assertNotNull($readResponse->data->changedBuildings[0]->address);

        return new ServerData($buildings, $maps, $craftsmen, $issues);
    }

    /**
     * checks if the issue is of the expected form.
     *
     * @param $checkIssue
     * @param Issue $issue
     */
    protected function verifyIssue($checkIssue, Issue $issue)
    {
        //check properties
        $this->assertSame($checkIssue->wasAddedWithClient, $issue->getWasAddedWithClient());
        $this->assertSame($checkIssue->isMarked, $issue->getIsMarked());
        $this->assertSame($checkIssue->imageFilename, $issue->getImageFilename());
        $this->assertSame($checkIssue->description, $issue->getDescription());
        $this->assertSame($checkIssue->map, $issue->getMap());

        //check meta is newer/equal & id is preserved
        $this->assertSame($checkIssue->meta->id, $issue->getMeta()->getId());
        $this->assertTrue($checkIssue->meta->lastChangeTime >= $issue->getMeta()->getLastChangeTime());

        //check position transferred correctly
        if (null !== $issue->getPosition()) {
            $this->assertNotNull($checkIssue->position);
            $this->assertSame($checkIssue->position->x, $issue->getPosition()->getX());
            $this->assertSame($checkIssue->position->y, $issue->getPosition()->getY());
            $this->assertSame((float)$checkIssue->position->zoomScale, $issue->getPosition()->getZoomScale());
        } else {
            $this->assertNull($checkIssue->position);
        }

        //check status
        $this->assertNotNull($checkIssue->status);
    }

    /**
     * generates a new guid (database id)
     *
     * @return mixed|null|string|string[]
     */
    protected function getNewGuid()
    {
        return mb_strtoupper(Uuid::uuid4()->toString());
    }

    /**
     * split up passed issue list into respective lists
     *
     * @param Issue[] $issues
     * @param Issue[] $newIssues
     * @param Issue[] $registeredIssues
     * @param Issue[] $respondedIssues
     * @param Issue[] $reviewedIssues
     */
    protected function categorizeIssues($issues, &$newIssues, &$registeredIssues, &$respondedIssues, &$reviewedIssues)
    {
        $newIssues = [];
        $registeredIssues = [];
        $respondedIssues = [];
        $reviewedIssues = [];

        foreach ($issues as $issue) {
            if (null !== $issue->getStatus()->getReview()) {
                $reviewedIssues[] = $issue;
            } elseif (null !== $issue->getStatus()->getResponse()) {
                $respondedIssues[] = $issue;
            } elseif (null !== $issue->getStatus()->getRegistration()) {
                $registeredIssues[] = $issue;
            } else {
                $newIssues[] = $issue;
            }
        }
    }
}