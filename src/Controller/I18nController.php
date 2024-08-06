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
use App\Helper\DoctrineHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class I18nController extends BaseController
{
    /**
     * @return Response
     */
    #[Route(path: '/set_locale/{locale}', name: 'set_locale')]
    public function setLocale(Request $request, string $locale, ManagerRegistry $registry): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        // only change locale to valid values
        if (\in_array($locale, ['de', 'it'], true)) {
            $request->getSession()->set('_locale', $locale);
            $request->setLocale($locale);

            $user = $this->getUser();
            if ($user) {
                $user->setLocale($locale);
                DoctrineHelper::persistAndFlush($registry, $user);
            }
        }

        return $this->redirect($request->query->get('return_to', $this->generateUrl('index')));
    }
}
