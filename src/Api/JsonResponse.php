<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/24/18
 * Time: 4:21 PM
 */

namespace App\Api;

class JsonResponse extends \Symfony\Component\HttpFoundation\JsonResponse
{
    protected $encodingOptions = self::DEFAULT_ENCODING_OPTIONS | JSON_UNESCAPED_UNICODE;
}
