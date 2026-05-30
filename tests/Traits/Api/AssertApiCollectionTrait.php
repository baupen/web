<?php

namespace App\Tests\Traits\Api;

use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Bundle\Test\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

trait AssertApiCollectionTrait
{
    private function assertApiCollectionContainsResponseItem(Client $client, string $url, ResponseInterface $itemResponse): void
    {
        $item = json_decode($itemResponse->getContent(), true);
        unset($item['@context']);
        unset($item['lastChangedAt']);

        $this->assertApiCollectionContains($client, $url, $item, 'lastChangedAt');
    }

    private function assertApiCollectionContainsResponseItemDeleted(Client $client, string $url, ResponseInterface $itemResponse): void
    {
        $item = json_decode($itemResponse->getContent(), true);
        unset($item['@context']);
        unset($item['lastChangedAt']);
        $item['isDeleted'] = true;

        $this->assertApiCollectionContains($client, $url, $item, 'lastChangedAt');
    }

    private function assertApiCollectionContainsIri(Client $client, string $url, string $iri): void
    {
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);

        foreach ($collection['hydra:member'] as $entry) {
            if ($entry['@id'] == $iri) {
                $this->assertTrue(true);

                return;
            }
        }

        $this->fail('iri ' . $iri . ' not found in ' . $collectionResponse->getContent());
    }

    private function assertApiCollectionNotContainsIri(Client $client, string $url, string $iri): void
    {
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);

        foreach ($collection['hydra:member'] as $entry) {
            if ($entry['@id'] == $iri) {
                $this->fail('iri ' . $iri . ' found in ' . $collectionResponse->getContent());
            }
        }

        $this->assertTrue(true);
    }

    private function assertApiCollectionContains(Client $client, string $url, array $item, string ...$excludedFields): void
    {
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);
        foreach ($collection['hydra:member'] as $entry) {
            $compareEntry = array_diff_key($entry, array_flip($excludedFields)); // remove excluded keys
            if ($compareEntry === $item) {
                $this->assertTrue(true);

                return;
            }
        }

        $this->fail('item ' . json_encode($item) . ' not found in collection ' . $collectionResponse->getContent());
    }
}
