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
    private function assertApiCollectionContainsItem(Client $client, string $url, Response $itemResponse)
    {
        $item = json_decode($itemResponse->getContent(), true);
        unset($item['@context']);

        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);
        foreach ($collection['hydra:member'] as $entry) {
            if ($entry == $item) {
                $this->assertTrue($entry == $item);

                return;
            }
        }

        $this->fail('item '.$itemResponse->getContent().' not found in collection '.$collectionResponse->getContent());
    }

    private function assertApiCollectionHasNoItemWithId(Client $client, string $url, string $id)
    {
        $collectionResponse = $this->assertApiGetOk($client, $url);
        $collection = json_decode($collectionResponse->getContent(), true);

        foreach ($collection['hydra:member'] as $entry) {
            if ($entry['@id'] == $id) {
                $this->fail('id '.$id.' found in '.$collectionResponse->getContent());

                return;
            }
        }

        $this->assertTrue(true);
    }
}
