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
use App\Service\Interfaces\EmailServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/email")
 */
class EmailController extends BaseController
{
    /**
     * @Route("/{identifier}", name="external_email")
     *
     * @param $identifier
     *
     * @return Response
     */
    public function emailAction($identifier, EmailServiceInterface $emailService)
    {
        $email = $this->getDoctrine()->getRepository('App:Email')->findOneBy(['id' => $identifier]);
        if (null === $email) {
            throw new NotFoundHttpException();
        }

        $content = $emailService->renderEmail($email);

        return new Response($content);
    }
}
