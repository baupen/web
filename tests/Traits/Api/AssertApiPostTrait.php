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
use Symfony\Component\HttpFoundation\Response as StatusCode;

trait AssertApiPostTrait
{
    private function assertApiPostStatusCodeSame(int $expectedCode, Client $client, string $url, array $payload)
    {
        return $this->assertApiStatusCodeSame('POST', $expectedCode, $client, $url, $payload);
    }

    private function assertApiPostPayloadMinimal(Client $client, string $url, array $payload, array $accessControlPayload = [])
    {
        foreach ($payload as $key => $value) {
            $actualPayload = array_merge($payload, $accessControlPayload);
            unset($actualPayload[$key]);

            $this->assertApiPostStatusCodeSame(StatusCode::HTTP_BAD_REQUEST, $client, $url, $actualPayload);
        }

        foreach ($accessControlPayload as $key => $value) {
            $actualPayload = array_merge($payload, $accessControlPayload);
            unset($actualPayload[$key]);

            $this->assertApiPostStatusCodeSame(StatusCode::HTTP_FORBIDDEN, $client, $url, $actualPayload);
        }
    }

    private function assertApiPostPayloadPersisted(Client $client, string $url, array $payload, array $additionalPayload = [])
    {
        $actualPayload = array_merge($payload, $additionalPayload);
        $response = $this->assertApiPostStatusCodeSame(StatusCode::HTTP_CREATED, $client, $url, $actualPayload);
        $this->assertJsonContains($payload);

        return $response;
    }
}
