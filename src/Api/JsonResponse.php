<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api;

class JsonResponse extends \Symfony\Component\HttpFoundation\JsonResponse
{
    protected $encodingOptions = self::DEFAULT_ENCODING_OPTIONS | JSON_UNESCAPED_UNICODE;
}
