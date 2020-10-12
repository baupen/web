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

use App\Controller\Base\BaseDoctrineController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class I18nController extends BaseDoctrineController
{
    /**
     * @Route("/set_locale/{locale}", name="set_locale")
     *
     * @param $locale
     *
     * @return Response
     */
    public function setLocaleAction(Request $request, $locale)
    {
        //only change locale to valid values
        if (\in_array($locale, ['de', 'it'], true)) {
            $request->getSession()->set('_locale', $locale);
            $request->setLocale($locale);
            $this->getUser()->setLocale($locale);
            $this->fastSave($this->getUser());
        }

        return $this->redirect($request->query->get('return_to', $this->generateUrl('index')));
    }
}
