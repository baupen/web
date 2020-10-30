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

trait AssertApiGetTrait
{
    private function assertApiGetOk(Client $client, string $url)
    {
        return $this->assertApiGetStatusCodeSame(StatusCode::HTTP_OK, $client, $url);
    }

    private function assertApiGetStatusCodeSame(int $expectedCode, Client $client, string $url)
    {
        return $this->assertApiStatusCodeSame('GET', $expectedCode, $client, $url);
    }
}
