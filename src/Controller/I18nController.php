<?php

namespace App\Controller;

use App\Helper\DoctrineHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class I18nController extends AbstractController
{
    #[Route(path: '/set_locale/{locale}', name: 'set_locale')]
    public function setLocale(Request $request, string $locale, ManagerRegistry $registry): RedirectResponse
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
