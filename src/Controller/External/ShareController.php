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

use App\Controller\Base\BaseDoctrineController;
use App\Entity\Craftsman;
use App\Entity\Filter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/share")
 */
class ShareController extends BaseDoctrineController
{
    /**
     * @Route("/c/{identifier}", name="external_share_craftsman")
     *
     * @param $identifier
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareAction($identifier)
    {
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['emailIdentifier' => $identifier]);
        if ($craftsman !== null) {
            return $this->forCraftsman($craftsman);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @Route("/f/{filter}", name="external_share_filter")
     */
    public function shareFilterAction(Filter $filter)
    {
        if ($filter->getShareAccessLimit() !== null && $filter->getShareAccessLimit() < new \DateTime()) {
            throw new NotFoundHttpException();
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param Craftsman $craftsman
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function forCraftsman(Craftsman $craftsman)
    {
        $craftsman->setLastOnlineVisit(new \DateTime());
        $this->fastSave($craftsman);

        return $this->render('share/craftsman.html.twig');
    }
}
