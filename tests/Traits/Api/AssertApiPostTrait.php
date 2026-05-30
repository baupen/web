<?php

namespace App\Tests\Traits\Api;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Service\Sample\AssetFile;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as StatusCode;
use Symfony\Contracts\HttpClient\ResponseInterface;

trait AssertApiPostTrait
{
    private function assertApiPostStatusCodeSame(int $expectedCode, Client $client, string $url, array $payload): ResponseInterface
    {
        return $this->assertApiStatusCodeSame('POST', $expectedCode, $client, $url, MimeTypes::JSON_LD_MIME_TYPE, $payload);
    }

    private function assertApiPostPayloadMinimal(int $expectedCode, Client $client, string $url, array $payload, array $includeAlways = []): void
    {
        foreach (array_keys($payload) as $key) {
            $actualPayload = array_merge($payload, $includeAlways);
            unset($actualPayload[$key]);

            $this->assertApiPostStatusCodeSame($expectedCode, $client, $url, $actualPayload);
        }
    }

    private function assertApiPostPayloadPersisted(Client $client, string $url, array $payload, array $additionalPayload = []): ResponseInterface
    {
        $actualPayload = array_merge($payload, $additionalPayload);
        $response = $this->assertApiPostStatusCodeSame(StatusCode::HTTP_CREATED, $client, $url, $actualPayload);
        $this->assertJsonContains($payload);

        return $response;
    }

    private function assertApiPostFile(KernelBrowser $kernelBrowser, string $url, AssetFile $file): false|string
    {
        $kernelBrowser->request(Request::METHOD_POST, $url, [], ['file' => $file]);

        $this->assertEquals(StatusCode::HTTP_CREATED, $kernelBrowser->getResponse()->getStatusCode());

        return $kernelBrowser->getResponse()->getContent();
    }

    private function assertApiDeleteFile(KernelBrowser $kernelBrowser, string $url): void
    {
        $kernelBrowser->request(Request::METHOD_DELETE, $url);

        $this->assertEquals(StatusCode::HTTP_NO_CONTENT, $kernelBrowser->getResponse()->getStatusCode());
    }
}
