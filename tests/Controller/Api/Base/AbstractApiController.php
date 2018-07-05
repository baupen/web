<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/26/18
 * Time: 8:11 PM
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
        $content = str_replace("\u003E", ">", $response->getContent());
        $this->assertFalse(mb_strpos($content, "\u00") > 0, mb_strpos($content, "\u00"));
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