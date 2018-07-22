<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External\Share;

use App\Controller\Base\BaseDoctrineController;
use App\Controller\External\Traits\CraftsmanAuthenticationTrait;
use App\Entity\Craftsman;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/c/{identifier}")
 */
class CraftsmanController extends BaseDoctrineController
{
    use CraftsmanAuthenticationTrait;

    /**
     * @Route("", name="external_share_craftsman")
     *
     * @param $identifier
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareAction($identifier)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $craftsman, $errorResponse)) {
            throw new NotFoundHttpException();
        }

        $craftsman->setLastOnlineVisit(new \DateTime());
        $this->fastSave($craftsman);

        return $this->render('share/craftsman.html.twig');
    }
}
