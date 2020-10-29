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
        $this->assertApiResponseStatusCodeSame(StatusCode::HTTP_METHOD_NOT_ALLOWED, $client, $url, ...$methods);
    }

    private function assertApiOperationNotAuthorized(Client $client, string $url, string ...$methods)
    {
        $this->assertApiResponseStatusCodeSame(StatusCode::HTTP_UNAUTHORIZED, $client, $url, ...$methods);
    }

    private function assertApiPostPayloadMinimal(Client $client, string $url, array $payload)
    {
        foreach ($payload as $key => $value) {
            $actualPayload = $payload;
            unset($actualPayload[$key]);

            $this->assertApiPostResponseCodeSame(StatusCode::HTTP_BAD_REQUEST, $client, $url, $actualPayload);
        }
    }

    private function getIriFromItem($item)
    {
        return static::$container->get('api_platform.iri_converter')->getIriFromItem($item);
    }

    private function assertApiPostPayloadPersisted(Client $client, string $url, array $payload)
    {
        $response = $this->assertApiPostResponseCodeSame(StatusCode::HTTP_CREATED, $client, $url, $payload);
        $this->assertJsonContains($payload);

        return $response;
    }

    private function assertApiResponseStatusCodeSame(int $expectedCode, Client $client, string $url, string ...$methods)
    {
        foreach ($methods as $method) {
            $client->request($method, $url, [
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            $this->assertResponseStatusCodeSame($expectedCode);
        }
    }

    private function assertApiGetResponseCodeSame(int $expectedCode, Client $client, string $url)
    {
        $client->request('GET', $url, [
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        $this->assertResponseStatusCodeSame($expectedCode);

        return $client->getResponse();
    }

    private function assertApiPostResponseCodeSame(int $expectedCode, Client $client, string $url, array $payload)
    {
        $client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);

        $this->assertResponseStatusCodeSame($expectedCode);

        return $client->getResponse();
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

            return;
        }

        $this->fail('only collections support this assertion.');
    }

    private function assertApiFileUrlIsDownloadable(Client $client, Response $response, string $fileUrlProperty): ?string
    {
        $content = $response->getContent();
        $hydraPayload = json_decode($content, true);

        if ('hydra:Collection' === $hydraPayload['@type']) {
            foreach ($hydraPayload['hydra:member'] as $member) {
                $url = $member[$fileUrlProperty] ?? null;
                if (!$url) {
                    continue;
                }

                $response = $client->request('GET', $url);
                $this->assertResponseStatusCodeSame(StatusCode::HTTP_OK);
                $this->assertStringStartsWith('inline', $response->getHeaders()['content-disposition'][0]);

                return $url;
            }

            $this->fail('no member has a the property '.$fileUrlProperty.' set, hence can not assert this url is valid.');

            return null;
        }

        $this->fail('only collections support this assertion.');

        return null;
    }
}
