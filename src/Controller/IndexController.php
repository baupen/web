<?php

namespace App\Controller;

use App\Controller\Base\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/')]
class IndexController extends BaseController
{
    #[Route(path: '', name: 'index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route(path: '/common.js', name: 'common_js')]
    public function commonJson(): Response
    {
        $response = $this->render('_common.js.twig');
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }
}
