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
use App\Entity\Map;
use App\Model\Breadcrumb;
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
class MapController extends BaseFrontendController
{
    /**
     * @Route("/", name="frontend_building_map_index")
     *
     * @param Building $building
     * @return Response
     */
    public function indexAction(Building $building)
    {
        $arr["building"] = $building;
        return $this->render('list.html.twig', $arr);
    }

    /**
     * @Route("/new", name="frontend_building_map_new")
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request, Building $building)
    {
        $map = new BuildingMap();
        $map->setBuilding($building);

        $form = $this->handleCreateForm(
            $request,
            $map
        );
        $arr["form"] = $form->createView();
        return $this->render('frontend/building/map/new.html.twig', $arr);
    }

    /**
     * @Route("/{map}/edit", name="frontend_building_map_edit")
     *
     * @param Request $request
     * @param BuildingMap $map
     * @return Response
     */
    public function editAction(Request $request, BuildingMap $map)
    {
        $form = $this->handleUpdateForm(
            $request,
            $map
        );
        $arr["form"] = $form->createView();
        $arr["map"] = $map;
        return $this->render('frontend/building/map/edit.html.twig', $arr);
    }

    /**
     * @Route("/{map}/remove", name="frontend_building_map_remove")
     *
     * @param Request $request
     * @param BuildingMap $map
     * @return Response
     */
    public function removeAction(Request $request, BuildingMap $map)
    {
        $removed = false;
        $form = $this->handleRemoveForm(
            $request,
            $map,
            function () use (&$removed) {
                $removed = true;
            }
        );
        if ($removed)
            return $this->redirectToRoute("frontend_building_map_index");

        $arr["form"] = $form->createView();
        $arr["map"] = $map;
        return $this->render('frontend/building/map/remove.html.twig', $arr);
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
                $this->generateUrl("frontend_building_map_index"),
                $translator->trans("index.title", [], "frontend_building_map")
            )
        ];
    }
}
