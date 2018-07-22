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
        if ($craftsman === null) {
            throw new NotFoundHttpException();
        }

        $craftsman->setLastOnlineVisit(new \DateTime());
        $this->fastSave($craftsman);

        return $this->render('share/craftsman.html.twig');
    }

    /**
     * @Route("/f/{filter}", name="external_share_filter")
     *
     * @param Filter $filter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareFilterAction(Filter $filter)
    {
        if ($filter->getAccessUntil() !== null && $filter->getAccessUntil() < new \DateTime()) {
            throw new NotFoundHttpException();
        }

        return $this->render('share/filter.html.twig');
    }
}
