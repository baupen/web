<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Frontend\Building;

use App\Controller\Frontend\Base\BaseFrontendController;
use App\Entity\Building;
use App\Entity\BuildingMap;
use App\Model\Breadcrumb;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/map")
 * @Security("has_role('ROLE_FRONTEND_USER')")
 *
 * @return Response
 */
class BaseBuildingController extends BaseFrontendController
{
    /**
     * @param Building $building
     * @return Breadcrumb[]
     */
    protected function getBuildingBreadcrumbs(Building $building)
    {
        return [new Breadcrumb(
            $this->generateUrl("frontend_building_view", ["building" => $building->getId()]),
            $building->getName()
        )];
    }


    /**
     * get the breadcrumbs leading to this controller
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        $translator = $this->getTranslator();

        return [
            new Breadcrumb(
                $this->generateUrl("frontend_dashboard_index"),
                $translator->trans("index.title", [], "frontend_dashboard")
            ),
            new Breadcrumb(
                $this->generateUrl("frontend_building_index"),
                $translator->trans("index.title", [], "frontend_building")
            )
        ];
    }
}
