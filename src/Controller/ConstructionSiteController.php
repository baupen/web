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
     */
    public function dashboard(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/dashboard.html.twig', ['constructionSite' => $constructionSite]);
    }

    /**
     * @Route("/foyer", name="construction_site_foyer")
     */
    public function foyer(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/foyer.html.twig', ['constructionSite' => $constructionSite]);
    }

    /**
     * @Route("/register", name="construction_site_register")
     */
    public function register(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/register.html.twig', ['constructionSite' => $constructionSite]);
    }

    /**
     * @Route("/dispatch", name="construction_site_dispatch")
     */
    public function dispatch(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/dispatch.html.twig', ['constructionSite' => $constructionSite]);
    }

    /**
     * @Route("/edit", name="construction_site_edit")
     */
    public function edit(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/edit.html.twig', ['constructionSite' => $constructionSite]);
    }
}
