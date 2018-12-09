<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api\External\Base;

use App\Api\External\Entity\ConstructionSite;
use App\Api\External\Entity\Craftsman;
use App\Api\External\Entity\Issue;
use App\Api\External\Entity\Map;
use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\ReadRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\AbstractApiController;
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
     * @throws \Exception
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

        $readRequest->setConstructionSites([]);
        $readRequest->setCraftsmen([]);
        $readRequest->setIssues([]);
        $readRequest->setMaps([]);

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($readResponse->data);
        $this->assertNotNull($readResponse->data->changedUser);
        $this->assertNotNull($readResponse->data->changedConstructionSites);
        $this->assertTrue(\count($readResponse->data->changedConstructionSites) > 0);
        $this->assertTrue(\count($readResponse->data->changedCraftsmen) > 0);
        $this->assertTrue(\count($readResponse->data->changedMaps) > 0);
        $this->assertTrue(\count($readResponse->data->changedIssues) > 0);

        $constructionSites = [];
        foreach ($readResponse->data->changedConstructionSites as $stdClass) {
            $constructionSites[] = $serializer->deserialize(json_encode($stdClass), ConstructionSite::class, 'json');
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

        $this->assertNotNull($readResponse->data->changedConstructionSites[0]->address);

        return new ServerData($constructionSites, $maps, $craftsmen, $issues);
    }

    /**
     * @param Map[] $maps
     * @return Map
     */
    protected function getMapWithFile($maps)
    {
        foreach ($maps as $map) {
            if ($map->getFile() !== null) {
                return $map;
            }
        }
        $this->fail("no map found with a file attached");
        return null;
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
        if ($issue->getImage() !== null) {
            $this->assertTrue(property_exists($checkIssue, "image"));
            $this->assertSame($checkIssue->image->id, $issue->getImage()->getId());
            $this->assertSame($checkIssue->image->filename, $issue->getImage()->getFilename());
        } else {
            $this->assertTrue(!property_exists($checkIssue, "image") || $checkIssue->image === null);
        }
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
     * generates a new guid (database id).
     *
     * @throws \Exception
     *
     * @return mixed|null|string|string[]
     */
    protected function getNewGuid()
    {
        return mb_strtoupper(Uuid::uuid4()->toString());
    }

    /**
     * split up passed issue list into respective lists.
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
