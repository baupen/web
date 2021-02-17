<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacyUrlController
{
    /**
     * @Route("/api/external/{route}", name="legacy_api", requirements={"route"=".+"})
     *
     * @return Response
     */
    public function indexAction()
    {
        $payload = new \stdClass();
        $payload->version = 2;

        return new JsonResponse($payload);
    }
}
