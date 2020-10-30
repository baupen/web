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
        $this->assertApiResponseStatusCodeSameForMethods(StatusCode::HTTP_METHOD_NOT_ALLOWED, $client, $url, ...$methods);
    }

    private function assertApiOperationNotAuthorized(Client $client, string $url, string ...$methods)
    {
        $this->assertApiResponseStatusCodeSameForMethods(StatusCode::HTTP_UNAUTHORIZED, $client, $url, ...$methods);
    }

    private function assertApiOperationForbidden(Client $client, string $url, string ...$methods)
    {
        $this->assertApiResponseStatusCodeSameForMethods(StatusCode::HTTP_FORBIDDEN, $client, $url, ...$methods);
    }

    private function assertApiPostPayloadMinimal(Client $client, string $url, array $payload, array $accessControlPayload = [])
    {
        foreach ($payload as $key => $value) {
            $actualPayload = array_merge($payload, $accessControlPayload);
            unset($actualPayload[$key]);

            $this->assertApiPostResponseStatusCodeSame(StatusCode::HTTP_BAD_REQUEST, $client, $url, $actualPayload);
        }

        foreach ($accessControlPayload as $key => $value) {
            $actualPayload = array_merge($payload, $accessControlPayload);
            unset($actualPayload[$key]);

            $this->assertApiPostResponseStatusCodeSame(StatusCode::HTTP_FORBIDDEN, $client, $url, $actualPayload);
        }
    }

    private function getIriFromItem($item)
    {
        return static::$container->get('api_platform.iri_converter')->getIriFromItem($item);
    }

    private function assertApiPostPayloadPersisted(Client $client, string $url, array $payload, array $additionalPayload = [])
    {
        $actualPayload = array_merge($payload, $additionalPayload);
        $response = $this->assertApiPostResponseStatusCodeSame(StatusCode::HTTP_CREATED, $client, $url, $actualPayload);
        $this->assertJsonContains($payload);

        return $response;
    }

    private function assertApiPatchPayloadPersisted(Client $client, string $url, array $payload)
    {
        $response = $this->assertApiPatchOk($client, $url, $payload);
        $this->assertJsonContains($payload);

        return $response;
    }

    private function assertApiResponseStatusCodeSameForMethods(int $expectedCode, Client $client, string $url, string ...$methods)
    {
        foreach ($methods as $method) {
            $this->assertApiResponseStatusCodeSame($method, $expectedCode, $client, $url);
        }
    }

    private function assertApiGetOk(Client $client, string $url)
    {
        return $this->assertApiGetResponseStatusCodeSame(StatusCode::HTTP_OK, $client, $url);
    }

    private function assertApiPatchOk(Client $client, string $url, array $payload)
    {
        return $this->assertApiPatchResponseStatusCodeSame(StatusCode::HTTP_OK, $client, $url, $payload);
    }

    private function assertApiDeleteOk(Client $client, string $url)
    {
        return $this->assertApiDeleteResponseStatusCodeSame(StatusCode::HTTP_NO_CONTENT, $client, $url);
    }

    private function assertApiGetResponseStatusCodeSame(int $expectedCode, Client $client, string $url)
    {
        return $this->assertApiResponseStatusCodeSame('GET', $expectedCode, $client, $url);
    }

    private function assertApiPostResponseStatusCodeSame(int $expectedCode, Client $client, string $url, array $payload)
    {
        return $this->assertApiResponseStatusCodeSame('POST', $expectedCode, $client, $url, $payload);
    }

    private function assertApiPatchResponseStatusCodeSame(int $expectedCode, Client $client, string $url, array $payload)
    {
        return $this->assertApiResponseStatusCodeSame('PATCH', $expectedCode, $client, $url, $payload);
    }

    private function assertApiDeleteResponseStatusCodeSame(int $expectedCode, Client $client, string $url)
    {
        return $this->assertApiResponseStatusCodeSame('DELETE', $expectedCode, $client, $url);
    }

    private function assertApiResponseStatusCodeSame(string $method, int $expectedCode, Client $client, string $url, array $payload = null)
    {
        $body = [];
        if ($payload) {
            $body['json'] = $payload;

            $contentType = 'application/json';
            if ('PATCH' === $method) {
                $contentType = 'application/merge-patch+json';
            }

            $body['headers'] = ['Content-Type' => $contentType];
        }

        $client->request($method, $url, $body);

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

                $response = $this->assertApiGetOk($client, $url);
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
