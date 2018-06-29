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

use App\Controller\Base\BaseDoctrineController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/construction_site")
 */
class ConstructionSiteController extends BaseDoctrineController
{
    /**
     * @Route("/list", name="construction_site_list")
     *
     * @return Response
     */
    public function listAction()
    {
        return $this->render('dispatch/dispatch.html.twig');
    }
}
