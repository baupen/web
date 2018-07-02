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
use App\Entity\Issue;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/share")
 */
class ShareController extends BaseDoctrineController
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
            return $this->forCraftsman($craftsman);
        }

        $filter = $this->getDoctrine()->getRepository('App:Filter')->findOneBy(['id' => $identifier]);
        if ($filter !== null) {
            throw new \InvalidArgumentException('not implemented yet');
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

        $filter = new Filter();
        $filter->setConstructionSite($craftsman->getConstructionSite()->getId());
        $filter->setCraftsmen([$craftsman->getId()]);
        $filter->setRespondedStatus(false);

        $arr['craftsman'] = $craftsman;
        $arr['issues'] = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);

        return $this->render('share/craftsman.html.twig', $arr);
    }
}
