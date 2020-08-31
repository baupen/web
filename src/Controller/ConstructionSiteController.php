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

use App\Controller\Base\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/construction_site/{construction_site}")
 */
class ConstructionSiteController extends BaseController
{
    /**
     * @Route("/dashboard", name="construction_site_dashboard")
     *
     * @return Response
     */
    public function dashboardAction()
    {
        return $this->render('construction_site/dashboard.html.twig');
    }

    /**
     * @Route("/foyer", name="construction_site_foyer")
     *
     * @return Response
     */
    public function foyerAction()
    {
        return $this->render('construction_site/foyer.html.twig');
    }

    /**
     * @Route("/register", name="construction_site_register")
     *
     * @return Response
     */
    public function registerAction()
    {
        return $this->render('construction_site/register.html.twig');
    }

    /**
     * @Route("/edit", name="construction_site_edit")
     *
     * @return Response
     */
    public function editAction()
    {
        return $this->render('construction_site/edit.html.twig');
    }

    /**
     * @Route("/dispatch", name="construction_site_dispatch")
     *
     * @return Response
     */
    public function dispatchAction()
    {
        return $this->render('construction_site/dispatch.html.twig');
    }
}
