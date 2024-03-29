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

trait AssertApiDeleteTrait
{
    private function assertApiDeleteOk(Client $client, string $url)
    {
        return $this->assertApiDeleteStatusCodeSame(StatusCode::HTTP_NO_CONTENT, $client, $url);
    }

    private function assertApiDeleteStatusCodeSame(int $expectedCode, Client $client, string $url)
    {
        return $this->assertApiStatusCodeSame('DELETE', $expectedCode, $client, $url);
    }
}
