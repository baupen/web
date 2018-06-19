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
use Ramsey\Uuid\Uuid;
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
class MapController extends BaseBuildingController
{
    /**
     * @Route("/new", name="frontend_building_map_new")
     *
     * @param Request $request
     * @param Building $building
     * @return Response
     */
    public function newAction(Request $request, Building $building)
    {
        $map = new BuildingMap();
        $map->setBuilding($building);
        $map->publish();

        $form = $this->handleCreateForm(
            $request,
            $map,
            function ($manager) use ($map) {
                /* @var EntityManager $manager */
                $file = $map->getFile();
                if ($file != null) {
                    if ($file->getError() != UPLOAD_ERR_OK) {
                        $this->displayError(
                            $this->getTranslator()->trans("edit.error.upload_failed", [], "frontend_building_map")
                        );
                        return false;
                    }

                    //persist so we get an id
                    $manager->persist($map);
                    $manager->flush();

                    //create filename & move the file
                    $fileName = Uuid::uuid4()->toString() . '.' . $file->guessExtension();
                    $file->move(
                        $this->getParameter('UPLOAD_DIR'),
                        $fileName
                    );

                    //store the filename
                    $map->setFileName($fileName);
                    return true;
                } else {
                    $this->displayError(
                        $this->getTranslator()->trans("new.error.no_file", [], "frontend_building_map")
                    );
                    return false;
                }
            }
        );
        $arr["form"] = $form->createView();
        return $this->render(
            'frontend/building/map/new.html.twig',
            $arr,
            null,
            $this->getBuildingBreadcrumbs($building)
        );
    }

    /**
     * @Route("/{map}/edit", name="frontend_building_map_edit")
     *
     * @param Request $request
     * @param Building $building
     * @param BuildingMap $map
     * @return Response
     */
    public function editAction(Request $request, Building $building, BuildingMap $map)
    {
        $form = $this->handleUpdateForm(
            $request,
            $map,
            function ($manager) use ($map) {
                /* @var EntityManager $manager */
                $file = $map->getFile();
                if ($file != null) {
                    if ($file->getError() != UPLOAD_ERR_OK) {
                        $this->displayError(
                            $this->getTranslator()->trans("edit.error.upload_failed", [], "frontend_building_map")
                        );
                        return false;
                    }
                    //persist so we get an id
                    $manager->persist($map);
                    $manager->flush();

                    //create filename & move the file
                    $fileName = Uuid::uuid4()->toString() . '.' . $file->guessExtension();
                    $file->move(
                        $this->getParameter('UPLOAD_DIR'),
                        $fileName
                    );

                    //store the filename
                    $map->setFileName($fileName);
                }
                return true;
            }
        );
        $arr["form"] = $form->createView();
        $arr["map"] = $map;
        return $this->render(
            'frontend/building/map/edit.html.twig',
            $arr,
            null,
            $this->getBuildingBreadcrumbs($building)
        );
    }

    /**
     * @Route("/{map}/markers", name="frontend_building_map_markers")
     *
     * @param Building $building
     * @param BuildingMap $map
     * @return Response
     */
    public function markersAction(Building $building, BuildingMap $map)
    {
        $arr["building"] = $building;
        $arr['map'] = $map;
        foreach ($map->getMarkers() as $marker) {
            if (!$marker->getViewedOnline()) {
                $marker->setViewedOnline(true);
                $this->fastSave($marker);
            }
        }
        return $this->render(
            'frontend/building/map/markers.html.twig',
            $arr,
            null,
            $this->getBuildingBreadcrumbs($building)
        );
    }

    /**
     * @Route("/{map}/remove", name="frontend_building_map_remove")
     *
     * @param Request $request
     * @param Building $building
     * @param BuildingMap $map
     * @return Response
     */
    public function removeAction(Request $request, Building $building, BuildingMap $map)
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
            return $this->redirectToRoute("frontend_building_view", ["building" => $building->getId()]);

        $arr["form"] = $form->createView();
        $arr["map"] = $map;
        return $this->render(
            'frontend/building/map/remove.html.twig',
            $arr,
            null,
            $this->getBuildingBreadcrumbs($building)
        );
    }
}
