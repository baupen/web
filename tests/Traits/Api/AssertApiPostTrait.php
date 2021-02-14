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
use App\DataFixtures\Model\AssetFile;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response as StatusCode;

trait AssertApiPostTrait
{
    private function assertApiPostStatusCodeSame(int $expectedCode, Client $client, string $url, array $payload)
    {
        return $this->assertApiStatusCodeSame('POST', $expectedCode, $client, $url, $payload);
    }

    private function assertApiPostPayloadMinimal(int $expectedCode, Client $client, string $url, array $payload, array $includeAlways = [])
    {
        foreach ($payload as $key => $value) {
            $actualPayload = array_merge($payload, $includeAlways);
            unset($actualPayload[$key]);

            $this->assertApiPostStatusCodeSame($expectedCode, $client, $url, $actualPayload);
        }
    }

    private function assertApiPostPayloadPersisted(Client $client, string $url, array $payload, array $additionalPayload = [])
    {
        $actualPayload = array_merge($payload, $additionalPayload);
        $response = $this->assertApiPostStatusCodeSame(StatusCode::HTTP_CREATED, $client, $url, $actualPayload);
        $this->assertJsonContains($payload);

        return $response;
    }

    private function assertApiPostFile(KernelBrowser $kernelBrowser, string $url, AssetFile $file)
    {
        $kernelBrowser->request('POST', $url, [], ['file' => $file]);

        $this->assertEquals(StatusCode::HTTP_CREATED, $kernelBrowser->getResponse()->getStatusCode());

        return $kernelBrowser->getResponse()->getContent();
    }
}
