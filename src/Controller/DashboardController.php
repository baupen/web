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

use App\Controller\Base\BaseLoginController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 */
class DashboardController extends BaseLoginController
{
    /**
     * @Route("", name="dashboard")
     *
     * @return Response
     */
    public function indexAction()
    {
        if ($this->getUser()->getActiveConstructionSite() === null) {
            return $this->redirectToRoute('switch');
        }

        return $this->render('dashboard/dashboard.html.twig');
    }
}
