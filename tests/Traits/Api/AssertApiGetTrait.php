<?php

namespace App\Tests\Traits\Api;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response as StatusCode;

trait AssertApiGetTrait
{
    private function assertApiGetOk(Client $client, string $url, string $acceptHeader = MimeTypes::JSON_LD_MIME_TYPE)
    {
        return $this->assertApiGetStatusCodeSame(StatusCode::HTTP_OK, $client, $url, $acceptHeader);
    }

    private function assertApiGetStatusCodeSame(int $expectedCode, Client $client, string $url, string $acceptHeader = MimeTypes::JSON_LD_MIME_TYPE)
    {
        return $this->assertApiStatusCodeSame('GET', $expectedCode, $client, $url, $acceptHeader);
    }
}
