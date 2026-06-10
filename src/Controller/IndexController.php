<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/')]
class IndexController extends AbstractController
{
    #[Route(path: '', name: 'index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route(path: '/index.js', name: 'index_js')]
    public function indexJs(): Response
    {
        $response = $this->render('index.js.twig');
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }
}
