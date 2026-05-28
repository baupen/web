<?php

namespace App\Controller;

use App\Controller\Base\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/help')]
class HelpController extends BaseController
{
    #[Route(path: '/welcome', name: 'help_welcome')]
    public function welcome(): Response
    {
        return $this->render('help/welcome.html.twig');
    }
}
