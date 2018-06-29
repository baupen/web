<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External;

use App\Controller\Base\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/view_issues")
 */
class ViewIssuesController extends BaseController
{
    /**
     * @Route("/{identifier}", name="external_view_issues")
     *
     * @param $identifier
     *
     * @return Response
     */
    public function viewIssuesAction($identifier)
    {
        $email = $this->getDoctrine()->getRepository('App:Filter')->findOneBy(['id' => $identifier]);
        if ($email === null) {
            throw new NotFoundHttpException();
        }

        return $this->render('email/email.html.twig', ['email' => $email]);
    }
}
