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
use Symfony\Component\HttpFoundation\Response;

trait AssertApiTokenTrait
{
    private function assertApiTokenRequestSuccessful(Client $client, string $token, string $method, string $url, ?array $payload = null)
    {
        $response = $this->requestWithApiToken($client, $token, $method, $url, $payload);

        $this->assertResponseIsSuccessful();

        return $response;
    }

    private function assertApiTokenRequestForbidden(Client $client, string $token, string $method, string $url, ?array $payload = null)
    {
        $response = $this->requestWithApiToken($client, $token, $method, $url, $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        return $response;
    }

    private function assertApiTokenRequestNotFound(Client $client, string $token, string $method, string $url, ?array $payload = null)
    {
        $response = $this->requestWithApiToken($client, $token, $method, $url, $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        return $response;
    }

    private function setApiTokenDefaultHeader(Client $client, string $token): void
    {
        $client->setDefaultOptions(['headers' => ['X-AUTHENTICATION' => $token]]);
    }

    private function requestWithApiToken(Client $client, string $token, string $method, string $url, ?array $payload = null): \Symfony\Contracts\HttpClient\ResponseInterface
    {
        $body = ['headers' => ['X-AUTHENTICATION' => $token]];
        if (is_array($payload)) {
            $body['json'] = $payload;

            $contentType = 'application/json';
            if ('PATCH' === $method) {
                $contentType = 'application/merge-patch+json';
            }

            $body['headers'] += ['Content-Type' => $contentType];
        }

        return $client->request($method, $url, $body);
    }
}
