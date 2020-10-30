<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response;

trait AssertApiCollectionTrait
{
    private function assertApiCollectionContainsResponseItem(Client $client, string $url, Response $itemResponse)
    {
        $item = json_decode($itemResponse->getContent(), true);
        unset($item['@context']);

        $this->assertApiCollectionContains($client, $url, $item);
    }

    private function assertApiCollectionContainsResponseItemDeleted(Client $client, string $url, Response $itemResponse)
    {
        $item = json_decode($itemResponse->getContent(), true);
        unset($item['@context']);
        $item['isDeleted'] = true;

        $this->assertApiCollectionContains($client, $url, $item);
    }

    private function assertApiCollectionContainsIri(Client $client, string $url, string $iri)
    {
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);

        foreach ($collection['hydra:member'] as $entry) {
            if ($entry['@id'] == $iri) {
                $this->assertTrue(true);

                return;
            }
        }

        $this->fail('iri '.$iri.' not found in '.$collectionResponse->getContent());
    }

    private function assertApiCollectionNotContainsIri(Client $client, string $url, string $iri)
    {
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);

        foreach ($collection['hydra:member'] as $entry) {
            if ($entry['@id'] == $iri) {
                $this->fail('iri '.$iri.' found in '.$collectionResponse->getContent());

                return;
            }
        }

        $this->assertTrue(true);
    }

    private function assertApiCollectionContains(Client $client, string $url, array $item)
    {
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);
        foreach ($collection['hydra:member'] as $entry) {
            if ($entry == $item) {
                $this->assertTrue($entry == $item);

                return;
            }
        }

        $this->fail('item '.json_encode($item).' not found in collection '.$collectionResponse->getContent());
    }
}
