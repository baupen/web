<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\Base;

use App\Controller\Base\BaseDoctrineController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AbstractApiController extends BaseDoctrineController
{
    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @final
     *
     * @param $data
     * @param int $status
     * @param array $headers
     * @param array $context
     *
     * @return JsonResponse
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        $serializer = $this->get('serializer');

        $json = $serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS | JSON_UNESCAPED_UNICODE,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}
