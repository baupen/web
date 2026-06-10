<?php

namespace App\Controller;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/construction_sites/{constructionSite}')]
class ConstructionSiteController extends AbstractController
{
    #[Route(path: '/shared.js', name: 'construction_site_shared_js')]
    public function js(ConstructionSite $constructionSite): Response
    {
        /** @var ConstructionManager $user */
        $user = $this->getUser();
        $response = $this->render('construction_site/shared.js.twig', ['constructionSite' => $constructionSite, "token" => $user->getAuthenticationToken()]);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    #[Route(path: '/dashboard', name: 'construction_site_dashboard')]
    public function dashboard(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/dashboard.html.twig', ['constructionSite' => $constructionSite]);
    }

    #[Route(path: '/foyer', name: 'construction_site_foyer')]
    public function foyer(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/foyer.html.twig', ['constructionSite' => $constructionSite]);
    }

    #[Route(path: '/register', name: 'construction_site_register')]
    public function register(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/register.html.twig', ['constructionSite' => $constructionSite]);
    }

    #[Route(path: '/dispatch', name: 'construction_site_dispatch')]
    public function dispatch(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/dispatch.html.twig', ['constructionSite' => $constructionSite]);
    }

    #[Route(path: '/edit', name: 'construction_site_edit')]
    public function edit(ConstructionSite $constructionSite): Response
    {
        return $this->render('construction_site/edit.html.twig', ['constructionSite' => $constructionSite]);
    }
}
