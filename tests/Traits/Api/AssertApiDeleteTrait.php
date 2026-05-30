<?php

namespace App\Tests\Traits\Api;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response as StatusCode;
use Symfony\Contracts\HttpClient\ResponseInterface;

trait AssertApiDeleteTrait
{
    private function assertApiDeleteOk(Client $client, string $url): ResponseInterface
    {
        return $this->assertApiDeleteStatusCodeSame(StatusCode::HTTP_NO_CONTENT, $client, $url);
    }

    private function assertApiDeleteStatusCodeSame(int $expectedCode, Client $client, string $url): ResponseInterface
    {
        return $this->assertApiStatusCodeSame('DELETE', $expectedCode, $client, $url);
    }
}
