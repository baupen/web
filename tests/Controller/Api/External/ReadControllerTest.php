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

use App\Api\External\Entity\Base\BaseEntity;
use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\ReadRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\External\Base\ApiController;
use Ramsey\Uuid\Uuid;

class ReadControllerTest extends ApiController
{
    /**
     * tests the create issue method.
     */
    public function testRead()
    {
        $client = static::createClient();
        $authenticatedUser = $this->getAuthenticatedUser($client);
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
        $serverData = $this->getServerEntities($client, $authenticatedUser);

        //## update none
        $readRequest = new ReadRequest();
        $readRequest->setAuthenticationToken($authenticatedUser->authenticationToken);
        $userMeta = new ObjectMeta();
        $userMeta->setId($authenticatedUser->meta->id);
        $userMeta->setLastChangeTime($authenticatedUser->meta->lastChangeTime);
        $readRequest->setUser($userMeta);

        //transform objects to meta object
        $getMetas = function (array $entities, $invalids = 1, $old = 0, $lost = 0) {
            //convert to object meta
            $metas = [];
            foreach ($entities as $entity) {
                /* @var BaseEntity $entity */
                if ($lost-- > 0) {
                    //skip to lose meta
                    continue;
                }
                $meta = new ObjectMeta();
                $meta->setId($entity->getMeta()->getId());
                if ($old-- > 0) {
                    //set to min datetime to force update
                    $meta->setLastChangeTime(((new \DateTime())->setTimestamp(0)->format('c')));
                } else {
                    $meta->setLastChangeTime($entity->getMeta()->getLastChangeTime());
                }
                $metas[] = $meta;
            }

            for ($i = 0; $i < $invalids; ++$i) {
                //create invalid & add
                $meta = new ObjectMeta();
                $meta->setId(Uuid::uuid4());
                $meta->setLastChangeTime((new \DateTime())->setTimestamp(0)->format('c'));
                $metas[] = $meta;
            }

            return $metas;
        };

        //set them in the request
        $readRequest->setConstructionSites($getMetas($serverData->getConstructionSites()));
        $readRequest->setCraftsmen($getMetas($serverData->getCraftsmen()));
        $readRequest->setMaps($getMetas($serverData->getMaps()));
        $readRequest->setIssues($getMetas($serverData->getIssues()));

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($readResponse->data);
        $this->assertNull($readResponse->data->changedUser);
        $this->assertEmpty($readResponse->data->changedBuildings);
        $this->assertEmpty($readResponse->data->changedCraftsmen);
        $this->assertEmpty($readResponse->data->changedMaps);
        $this->assertEmpty($readResponse->data->changedIssues);
        $this->assertCount(1, $readResponse->data->removedBuildingIDs);
        $this->assertCount(1, $readResponse->data->removedCraftsmanIDs);
        $this->assertCount(1, $readResponse->data->removedMapIDs);
        $this->assertCount(1, $readResponse->data->removedIssueIDs);

        //## update, remove & add at the same time
        //set them in the request
        $readRequest->setConstructionSites($getMetas($serverData->getConstructionSites(), 1, 1, 1));
        $readRequest->setCraftsmen($getMetas($serverData->getCraftsmen(), 1, 1, 1));
        $readRequest->setMaps($getMetas($serverData->getMaps(), 1, 1, 1));
        $readRequest->setIssues($getMetas($serverData->getIssues(), 1, 1, 1));

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($readResponse->data);
        $this->assertNull($readResponse->data->changedUser);
        $this->assertCount(2, $readResponse->data->changedBuildings);
        $this->assertCount(2, $readResponse->data->changedCraftsmen);
        $this->assertCount(2, $readResponse->data->changedMaps);
        $this->assertCount(2, $readResponse->data->changedIssues);
        $this->assertCount(1, $readResponse->data->removedBuildingIDs);
        $this->assertCount(1, $readResponse->data->removedCraftsmanIDs);
        $this->assertCount(1, $readResponse->data->removedMapIDs);
        $this->assertCount(1, $readResponse->data->removedIssueIDs);
    }
}
