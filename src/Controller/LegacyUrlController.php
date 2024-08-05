<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class LegacyUrlController extends BaseController
{
    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/api/external/{route}', name: 'legacy_api_exernal', requirements: ['route' => '.+'])]
    public function apiExternal(): JsonResponse
    {
        $payload = new \stdClass();
        $payload->version = 2;

        return new JsonResponse($payload);
    }

    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/external/share/c/{identifier}', name: 'legacy_external_share_c', requirements: ['route' => '.+'])]
    public function externalShareC(string $identifier, TranslatorInterface $translator): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->displayRelinkWarning($translator);

        return $this->redirectToRoute('public_resolve', ['token' => $identifier]);
    }

    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/{route}', name: 'legacy_dashboard', requirements: ['route' => '(dashboard)|(foyer)|(dispatch)|(edit)|(switch)'])]
    public function legacy(TranslatorInterface $translator): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->displayRelinkWarning($translator);

        return $this->redirectToRoute('index');
    }

    private function displayRelinkWarning(TranslatorInterface $translator): void
    {
        $routeChanged = $translator->trans('legacy.warning.redirected_from_legacy_url', [], 'framework');
        $this->displayWarning($routeChanged);
    }
}
