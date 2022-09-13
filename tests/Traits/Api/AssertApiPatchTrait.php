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

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response as StatusCode;

trait AssertApiPatchTrait
{
    private function assertApiPatchPayloadPersisted(Client $client, string $url, array $payload)
    {
        $response = $this->assertApiPatchOk($client, $url, $payload);
        $this->assertJsonContains($payload);

        return $response;
    }

    private function assertApiPatchPayloadIgnored(Client $client, string $url, array $payload)
    {
        $noChangeResponse = $this->assertApiPatchOk($client, $url, []);
        $json = json_decode($noChangeResponse->getContent(), true);

        if (isset($json['lastChangedAt'])) {
            unset($json['lastChangedAt']);
        }

        foreach ($payload as $key => $value) {
            $actualPayload = [$key => $value];
            $this->assertApiPatchOk($client, $url, $actualPayload);
            $this->assertJsonContains($json);
        }
    }

    private function assertApiPatchOk(Client $client, string $url, array $payload)
    {
        return $this->assertApiPatchStatusCodeSame(StatusCode::HTTP_OK, $client, $url, $payload);
    }

    private function assertApiPatchStatusCodeSame(int $expectedCode, Client $client, string $url, array $payload)
    {
        return $this->assertApiStatusCodeSame('PATCH', $expectedCode, $client, $url, MimeTypes::JSON_LD_MIME_TYPE, $payload);
    }
}
