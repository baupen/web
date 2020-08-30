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

use App\Controller\Base\BaseLoginController;
use App\Entity\ConstructionSite;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/switch")
 */
class SwitchController extends BaseLoginController
{
    /**
     * @Route("", name="switch")
     *
     * @return Response
     */
    public function indexAction()
    {
        if ($this->getUser()->getIsTrialAccount()) {
            // resolve #283
            $this->ensureActiveConstructionSiteSet();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('switch/switch.html.twig');
    }

    /**
     * @Route("/{constructionSite}", name="switch_switch")
     *
     * @return Response
     */
    public function switchAction(ConstructionSite $constructionSite)
    {
        $this->ensureAccess($constructionSite);

        //save new active construction site
        $user = $this->getUser();
        $user->setActiveConstructionSite($constructionSite);
        $this->fastSave($user);

        return $this->redirectToRoute('dashboard');
    }

    private function ensureActiveConstructionSiteSet(): void
    {
        $constructionManager = $this->getUser();

        if ($constructionManager->getActiveConstructionSite() === null) {
            $constructionManager->setActiveConstructionSite($constructionManager->getConstructionSites()->first());
            $this->fastSave($constructionManager);
        }
    }
}
