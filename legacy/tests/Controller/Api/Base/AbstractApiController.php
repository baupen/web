<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api\Base;

use App\Enum\ApiStatus;
use App\Tests\Controller\Base\FixturesTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController extends FixturesTestCase
{
    /**
     * @param Response $response
     * @param $apiStatus
     * @param string $message
     *
     * @return mixed|null
     */
    protected function checkResponse(Response $response, $apiStatus, $message = '')
    {
        $content = str_replace("\u003E", '>', $response->getContent());
        $this->assertFalse(mb_strpos($content, "\u00") > 0, 'invalid char at ' . mb_strpos($content, "\u00") . ' in string ' . $content);
        $this->assertTrue(mb_strpos($response->getContent(), '{"version":1') === 0);
        if (ApiStatus::SUCCESS === $apiStatus) {
            $successful = json_decode($response->getContent());
            $this->assertSame($apiStatus, $successful->status, $response->getContent());
            $this->assertSame(200, $response->getStatusCode());

            return $successful;
        } elseif (ApiStatus::FAIL === $apiStatus) {
            $failed = json_decode($response->getContent());
            $this->assertSame($apiStatus, $failed->status, $response->getContent());
            $this->assertSame($message, $failed->message);
            $this->assertSame(400, $response->getStatusCode());

            return $failed;
        } elseif (ApiStatus::ERROR === $apiStatus) {
            $error = json_decode($response->getContent());
            $this->assertSame($apiStatus, $error->status);
            $this->assertSame($message, $error->message);
            $this->assertNotSame(500, $response->getStatusCode());

            return $error;
        }

        return null;
    }
}
