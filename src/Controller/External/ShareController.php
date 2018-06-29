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
use App\Entity\Craftsman;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/share")
 */
class ShareController extends BaseController
{
    /**
     * @Route("/{identifier}", name="external_share")
     *
     * @param $identifier
     */
    public function shareAction($identifier)
    {
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['emailIdentifier' => $identifier]);
        if ($craftsman !== null) {
            throw new NotImplementedException('todo');
        }

        $filter = $this->getDoctrine()->getRepository('App:Filter')->findOneBy(['id' => $identifier]);
        if ($filter !== null) {
            throw new NotImplementedException('todo');
        }

        throw new NotFoundHttpException();
    }
}
