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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LegacyUrlController extends BaseController
{
    /**
     * @Route("/api/external/{route}", name="legacy_api_exernal", requirements={"route"=".+"})
     *
     * @return Response
     */
    public function apiExternalAction()
    {
        $payload = new \stdClass();
        $payload->version = 2;

        return new JsonResponse($payload);
    }

    /**
     * @Route("/external/share/c/{identifier}", name="legacy_external_share_c", requirements={"route"=".+"})
     *
     * @return Response
     */
    public function externalShareCAction(string $identifier, TranslatorInterface $translator)
    {
        $this->displayRelinkWarning($translator);

        return $this->redirectToRoute('public_resolve', ['token' => $identifier]);
    }

    /**
     * @Route("/{route}", name="legacy_dashboard", requirements={"route"="(dashboard)|(foyer)|(dispatch)|(edit)|(switch)"})
     *
     * @return Response
     */
    public function legacyAction(TranslatorInterface $translator)
    {
        $this->displayRelinkWarning($translator);

        return $this->redirectToRoute('index');
    }

    private function displayRelinkWarning(TranslatorInterface $translator)
    {
        $routeChanged = $translator->trans('legacy.warning.redirected_from_legacy_url', [], 'framework');
        $this->displayWarning($routeChanged);
    }
}
