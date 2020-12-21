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
use App\Entity\ConstructionSite;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/construction_sites/{constructionSite}")
 */
class ConstructionSiteController extends BaseController
{
    /**
     * @Route("/dashboard", name="construction_site_dashboard")
     *
     * @return Response
     */
    public function dashboardAction(ConstructionSite $constructionSite)
    {
        return $this->render('construction_site/dashboard.html.twig', ['constructionSite' => $constructionSite]);
    }

    /**
     * @Route("/foyer", name="construction_site_foyer")
     *
     * @return Response
     */
    public function foyerAction(ConstructionSite $constructionSite)
    {
        return $this->render('construction_site/foyer.html.twig', ['constructionSite' => $constructionSite]);
    }

    /**
     * @Route("/register", name="construction_site_register")
     *
     * @return Response
     */
    public function registerAction(ConstructionSite $constructionSite)
    {
        return $this->render('construction_site/register.html.twig', ['constructionSite' => $constructionSite]);
    }

    /**
     * @Route("/dispatch", name="construction_site_dispatch")
     *
     * @return Response
     */
    public function dispatchAction(ConstructionSite $constructionSite)
    {
        return $this->render('construction_site/dispatch.html.twig', ['constructionSite' => $constructionSite]);
    }

    /**
     * @Route("/edit", name="construction_site_edit")
     *
     * @return Response
     */
    public function editAction(ConstructionSite $constructionSite)
    {
        return $this->render('construction_site/edit.html.twig', ['constructionSite' => $constructionSite]);
    }
}
