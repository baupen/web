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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/help")
 */
class HelpController extends BaseController
{
    /**
     * @Route("/welcome", name="help_welcome")
     *
     * @return Response
     */
    public function welcomeAction()
    {
        return $this->render('help/welcome.html.twig');
    }
}
