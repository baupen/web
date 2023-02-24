<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits\Api;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response as StatusCode;

trait AssertApiOperationsTrait
{
    private function assertApiOperationUnsupported(Client $client, string $url, string ...$methods)
    {
        $this->assertApiOperationsStatusCodeSame(StatusCode::HTTP_METHOD_NOT_ALLOWED, $client, $url, ...$methods);
    }

    private function assertApiOperationNotAuthorized(Client $client, string $url, string ...$methods)
    {
        $this->assertApiOperationsStatusCodeSame(StatusCode::HTTP_UNAUTHORIZED, $client, $url, ...$methods);
    }

    private function assertApiOperationForbidden(Client $client, string $url, string ...$methods)
    {
        $this->assertApiOperationsStatusCodeSame(StatusCode::HTTP_FORBIDDEN, $client, $url, ...$methods);
    }

    private function assertApiOperationsStatusCodeSame(int $expectedCode, Client $client, string $url, string ...$methods)
    {
        foreach ($methods as $method) {
            if ('GET' === $method) {
                $this->assertApiStatusCodeSame($method, $expectedCode, $client, $url);
            } else {
                $this->assertApiStatusCodeSame($method, $expectedCode, $client, $url, MimeTypes::JSON_LD_MIME_TYPE, []);
            }
        }
    }

    private function assertApiStatusCodeSame(string $method, int $expectedCode, Client $client, string $url, string $acceptHeader = MimeTypes::JSON_LD_MIME_TYPE, array $payload = null)
    {
        $body = ['headers' => ['Accept' => $acceptHeader]];
        if (is_array($payload)) {
            $body['json'] = $payload;

            $contentType = 'application/json';
            if ('PATCH' === $method) {
                $contentType = 'application/merge-patch+json';
            }

            $body['headers'] = ['Content-Type' => $contentType];
        }

        $response = $client->request($method, $url, $body);

        $this->assertResponseStatusCodeSame($expectedCode);

        return $response;
    }
}
