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
    const TYPE_STRING = 1;
    const TYPE_INT = 2;
    const TYPE_BOOLEAN = 4;
    const TYPE_DOUBLE = 8;
    const TYPE_UUID = 16;
    const TYPE_DATE_TIME = 32;
    const TYPE_UUID_ARRAY = 64;
    const TYPE_NULLABLE = 128;

    /**
     * tests that only valid, specified properties are returned.
     *
     * @throws \Exception
     */
    public function testPublicProperties()
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

        //## update none
        $readRequest = new ReadRequest();
        $readRequest->setAuthenticationToken($authenticatedUser->authenticationToken);
        $userMeta = new ObjectMeta();
        $userMeta->setId($authenticatedUser->meta->id);
        $userMeta->setLastChangeTime($authenticatedUser->meta->lastChangeTime);
        $readRequest->setUser($userMeta);
        $readRequest->setConstructionSites([]);
        $readRequest->setCraftsmen([]);
        $readRequest->setIssues([]);
        $readRequest->setMaps([]);

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($readResponse->data);
        $this->assertNull($readResponse->data->changedUser);
        $this->assertNotEmpty($readResponse->data->changedConstructionSites);

        $metaDefinition = ['id' => self::TYPE_UUID, 'lastChangeTime' => self::TYPE_DATE_TIME];

        $this->assertPropertiesMatch($readResponse->data->changedConstructionSites, [
                'name' => self::TYPE_STRING,
                'address' => [
                    '_required' => false,
                    'streetAddress' => self::TYPE_STRING | self::TYPE_NULLABLE,
                    'postalCode' => self::TYPE_INT | self::TYPE_NULLABLE,
                    'locality' => self::TYPE_STRING | self::TYPE_NULLABLE,
                    'country' => self::TYPE_STRING | self::TYPE_NULLABLE,
                ],
                'maps' => self::TYPE_UUID_ARRAY,
                'craftsmen' => self::TYPE_UUID_ARRAY,
                'image' => ['id' => self::TYPE_UUID, 'filename' => self::TYPE_STRING],
                'meta' => $metaDefinition,
            ]
        );

        $this->assertPropertiesMatch(
            $readResponse->data->changedCraftsmen, [
                'name' => self::TYPE_STRING,
                'trade' => self::TYPE_STRING,
                'meta' => $metaDefinition,
            ]
        );

        $eventDefinition = ['time' => self::TYPE_DATE_TIME, 'author' => self::TYPE_STRING];
        $pointDefinition = ['x' => self::TYPE_DOUBLE, 'y' => self::TYPE_DOUBLE];
        $fileDefinition = ['id' => self::TYPE_UUID, 'filename' => self::TYPE_STRING];
        $this->assertPropertiesMatch(
            $readResponse->data->changedIssues, [
                'number' => self::TYPE_INT | self::TYPE_NULLABLE,
                'isMarked' => self::TYPE_BOOLEAN,
                'wasAddedWithClient' => self::TYPE_BOOLEAN,
                'image' => $fileDefinition,
                'description' => self::TYPE_STRING | self::TYPE_NULLABLE,
                'craftsman' => self::TYPE_UUID | self::TYPE_NULLABLE,
                'map' => self::TYPE_UUID,
                'status' => [
                    'registration' => $eventDefinition,
                    'response' => $eventDefinition,
                    'review' => $eventDefinition,
                ],
                'position' => ['point' => $pointDefinition, 'zoomScale' => self::TYPE_DOUBLE, 'mapFileId' => self::TYPE_UUID],
                'meta' => $metaDefinition,
            ]
        );

        $this->assertPropertiesMatch(
            $readResponse->data->changedMaps, [
                'name' => self::TYPE_STRING,
                'children' => self::TYPE_UUID_ARRAY,
                'issues' => self::TYPE_UUID_ARRAY,
                'file' => $fileDefinition,
                'sectors' => ['name' => self::TYPE_STRING, 'color' => self::TYPE_STRING, 'points' => $pointDefinition],
                'sectorFrame' => ['startX' => self::TYPE_DOUBLE, 'startY' => self::TYPE_DOUBLE, 'width' => self::TYPE_DOUBLE, 'height' => self::TYPE_DOUBLE],
                'meta' => $metaDefinition,
            ]
        );

        $this->assertNotEmpty($readResponse->data->changedCraftsmen);
        $this->assertNotEmpty($readResponse->data->changedMaps);
        $this->assertNotEmpty($readResponse->data->changedIssues);
    }

    private function assertPropertiesMatch($object, $expectedProperties)
    {
        $required = false;
        if (isset($expectedProperties['_required'])) {
            $required = $expectedProperties['_required'];
            unset($expectedProperties['_required']);
        }

        if (null === $object) {
            if ($required) {
                $this->fail('required object was not set');
            }

            return;
        }

        $checkValueType = function ($value, $type) {
        };

        $checkObject = function ($object) use ($expectedProperties, $checkValueType) {
            $properties = get_object_vars($object);
            $this->assertSameSize($expectedProperties, $properties, implode(', ', array_keys($properties)));

            foreach ($expectedProperties as $name => $type) {
                $this->assertTrue(property_exists($object, $name), 'property ' . $name . ' not found');

                $value = $object->$name;
                if (\is_array($type)) {
                    $this->assertPropertiesMatch($value, $type);
                } else {
                    if ($type & self::TYPE_NULLABLE && $value === null) {
                        // fine!
                    } elseif ($type & self::TYPE_STRING) {
                        $this->assertTrue(\is_string($value));
                    } elseif ($type & self::TYPE_INT) {
                        $this->assertTrue(\is_int($value));
                    } elseif ($type & self::TYPE_BOOLEAN) {
                        $this->assertTrue(\is_bool($value));
                    } elseif ($type & self::TYPE_DOUBLE) {
                        $this->assertTrue(\is_float($value) || \is_int($value));
                    } elseif ($type & self::TYPE_UUID) {
                        $this->assertTrue(\is_string($value));
                        $this->assertTrue(\mb_strlen(Uuid::NIL) === \mb_strlen($value));
                    } elseif ($type & self::TYPE_DATE_TIME) {
                        $this->assertTrue(\is_string($value));
                        $this->assertTrue(\mb_strlen('2018-12-09T14:22:47+01:00') === \mb_strlen($value));
                    } elseif ($type & self::TYPE_UUID_ARRAY) {
                        $this->assertTrue(\is_array($value));
                        foreach ($value as $item) {
                            $this->assertTrue(\mb_strlen(Uuid::NIL) === \mb_strlen($item));
                        }
                    }
                }
            }
        };

        if (\is_array($object)) {
            foreach ($object as $item) {
                $checkObject($item);
            }
        } else {
            $checkObject($object);
        }
    }

    /**
     * tests the read method.
     *
     * @throws \Exception
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
        $this->assertEmpty($readResponse->data->changedConstructionSites);
        $this->assertEmpty($readResponse->data->changedCraftsmen);
        $this->assertEmpty($readResponse->data->changedMaps);
        $this->assertEmpty($readResponse->data->changedIssues);
        $this->assertCount(1, $readResponse->data->removedConstructionSiteIDs);
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
        $this->assertCount(2, $readResponse->data->changedConstructionSites);
        $this->assertCount(2, $readResponse->data->changedCraftsmen);
        $this->assertCount(2, $readResponse->data->changedMaps);
        $this->assertCount(2, $readResponse->data->changedIssues);
        $this->assertCount(1, $readResponse->data->removedConstructionSiteIDs);
        $this->assertCount(1, $readResponse->data->removedCraftsmanIDs);
        $this->assertCount(1, $readResponse->data->removedMapIDs);
        $this->assertCount(1, $readResponse->data->removedIssueIDs);
    }
}
