<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/help')]
class HelpController extends AbstractController
{
    #[Route(path: '/welcome', name: 'help_welcome')]
    public function welcome(): Response
    {
        return $this->render('help/welcome.html.twig');
    }
}
