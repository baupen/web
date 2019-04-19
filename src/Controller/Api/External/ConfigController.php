<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\External;

use App\Controller\Api\External\Base\ExternalApiController;
use App\Service\Interfaces\PathServiceInterface;
use const DIRECTORY_SEPARATOR;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/config")
 *
 * @return Response
 */
class ConfigController extends ExternalApiController
{
    /**
     * @Route("/domain_overrides", name="api_external_config_domain_overrides", methods={"GET"})
     *
     * @param PathServiceInterface $pathService
     *
     * @return Response
     */
    public function domainOverridesAction(PathServiceInterface $pathService)
    {
        $domainOverridesPath = $pathService->getTransientFolderRoot() . DIRECTORY_SEPARATOR . 'domainOverrides.json';

        if (!file_exists($domainOverridesPath)) {
            throw new NotFoundHttpException($domainOverridesPath);
        }

        return new Response(file_get_contents($domainOverridesPath));
    }
}
