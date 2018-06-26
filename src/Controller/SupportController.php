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

use App\Controller\Base\BaseFormController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/support")
 *
 * @return Response
 */
class SupportController extends BaseFormController
{
    /**
     * @Route("", name="support")
     *
     * @return Response
     */
    public function supportAction()
    {
        return $this->render('support/support.html.twig');
    }
}
