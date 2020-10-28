<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response;
use Symfony\Component\HttpFoundation\Response as StatusCode;

trait AssertApiTrait
{
    private function assertApiOperationUnsupported(Client $client, string $url, string ...$methods)
    {
        $this->assertResponseStatusCodeSameForUrls(StatusCode::HTTP_METHOD_NOT_ALLOWED, $client, $url, ...$methods);
    }

    private function assertApiOperationNotAuthorized(Client $client, string $url, string ...$methods)
    {
        $this->assertResponseStatusCodeSameForUrls(StatusCode::HTTP_UNAUTHORIZED, $client, $url, ...$methods);
    }

    private function assertApiPostFieldsRequired(Client $client, string $url, $payload)
    {
        foreach ($payload as $key => $value) {
            $actualPayload = $payload;
            unset($actualPayload[$key]);

            $client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $actualPayload,
            ]);

            $this->assertResponseStatusCodeSame(StatusCode::HTTP_BAD_REQUEST);
        }
    }

    private function assertApiPostFieldsPersisted(Client $client, string $url, $payload)
    {
        $client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);

        $this->assertResponseStatusCodeSame(StatusCode::HTTP_CREATED);
        $this->assertJsonContains($payload);
    }

    private function assertResponseStatusCodeSameForUrls(int $expectedCode, Client $client, string $url, string ...$methods)
    {
        foreach ($methods as $method) {
            $client->request($method, $url, [
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            $this->assertResponseStatusCodeSame($expectedCode);
        }
    }

    private function assertContainsOnlyListedFields(Response $response, string ...$expectedFields)
    {
        $content = $response->getContent();
        $hydraPayload = json_decode($content, true);

        $whitelist = array_merge(['@id', '@type'], $expectedFields);
        sort($whitelist);

        if ('hydra:Collection' === $hydraPayload['@type']) {
            foreach ($hydraPayload['hydra:member'] as $member) {
                $actualFields = array_keys($member);
                sort($actualFields);

                $this->assertArraySubset($actualFields, $whitelist);
            }
        }
    }
}
