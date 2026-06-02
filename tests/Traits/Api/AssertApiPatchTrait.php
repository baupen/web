<?php

namespace App\Tests\Traits\Api;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response as StatusCode;
use Symfony\Contracts\HttpClient\ResponseInterface;

trait AssertApiPatchTrait
{
    private function assertApiPatchPayloadPersisted(Client $client, string $url, array $payload): ResponseInterface
    {
        $response = $this->assertApiPatchOk($client, $url, $payload);
        $this->assertJsonContains($payload);

        return $response;
    }

    private function assertApiPatchPayloadIgnored(Client $client, string $url, array $payload): void
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

    private function assertApiPatchOk(Client $client, string $url, array $payload): ResponseInterface
    {
        return $this->assertApiPatchStatusCodeSame(StatusCode::HTTP_OK, $client, $url, $payload);
    }

    private function assertApiPatchStatusCodeSame(int $expectedCode, Client $client, string $url, array $payload): ResponseInterface
    {
        return $this->assertApiStatusCodeSame('PATCH', $expectedCode, $client, $url, MimeTypes::JSON_LD_MIME_TYPE, $payload);
    }
}
