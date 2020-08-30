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
 * @Route("/dispatch")
 */
class DispatchController extends BaseDoctrineController
{
    /**
     * @Route("", name="dispatch")
     *
     * @return Response
     */
    public function dispatchAction()
    {
        return $this->render('dispatch/dispatch.html.twig');
    }
}
