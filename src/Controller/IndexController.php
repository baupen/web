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

#[\Symfony\Component\Routing\Attribute\Route(path: '/')]
class IndexController extends BaseController
{
    #[\Symfony\Component\Routing\Attribute\Route(path: '', name: 'index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }
}
