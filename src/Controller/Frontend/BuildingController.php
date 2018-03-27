<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Frontend;

use App\Controller\Frontend\Base\BaseFrontendController;
use App\Entity\Building;
use App\Entity\Craftsman;
use App\Entity\Marker;
use App\Model\Base\MarkerInfo;
use App\Model\Breadcrumb;
use App\Model\BuildingMap\BuildingMapMarkerInfo;
use App\Model\Craftsman\CraftsmanMarkerInfo;
use App\Service\Interfaces\EmailServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/building")
 * @Security("has_role('ROLE_FRONTEND_USER')")
 *
 * @return Response
 */
class BuildingController extends BaseFrontendController
{
    /**
     * @Route("/", name="frontend_building_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $arr["buildings"] = $this->getDoctrine()->getRepository(Building::class)->findBy(["isArchived" => false]);
        return $this->render('frontend/building/index.html.twig', $arr);
    }

    /**
     * @Route("/archived", name="frontend_building_archived")
     *
     * @return Response
     */
    public function archivedAction()
    {
        $arr["buildings"] = $this->getDoctrine()->getRepository(Building::class)->findBy(["isArchived" => true]);
        return $this->render('frontend/building/index_archived.html.twig', $arr);
    }

    /**
     * @Route("/new", name="frontend_building_new")
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $building = new Building();

        $form = $this->handleCreateForm(
            $request,
            $building
        );
        $arr["form"] = $form->createView();
        return $this->render('frontend/building/new.html.twig', $arr);
    }

    /**
     * @Route("/{building}/edit", name="frontend_building_edit")
     *
     * @param Request $request
     * @param Building $building
     * @return Response
     */
    public function editAction(Request $request, Building $building)
    {
        $form = $this->handleUpdateForm(
            $request,
            $building
        );
        $arr["form"] = $form->createView();
        $arr["building"] = $building;
        return $this->render('frontend/building/edit.html.twig', $arr);
    }

    /**
     * @Route("/{building}/remove", name="frontend_building_remove")
     *
     * @param Request $request
     * @param Building $building
     * @return Response
     */
    public function removeAction(Request $request, Building $building)
    {
        $removed = false;
        $form = $this->handleRemoveForm(
            $request,
            $building,
            function () use (&$removed) {
                $removed = true;
            }
        );
        if ($removed)
            return $this->redirectToRoute("frontend_building_index");

        $arr["form"] = $form->createView();
        $arr["building"] = $building;
        return $this->render('frontend/building/remove.html.twig', $arr);
    }

    /**
     * @Route("/{building}", name="frontend_building_view")
     *
     * @param Building $building
     * @return Response
     */
    public function viewAction(Building $building)
    {
        $arr["building"] = $building;
        return $this->render('frontend/building/view.html.twig', $arr);
    }

    /**
     * @Route("/{building}/un_archive", name="frontend_building_un_archive")
     *
     * @param Building $building
     * @return Response
     */
    public function unArchiveAction(Building $building)
    {
        $building->setIsArchived(false);
        $this->fastSave($building);
        return $this->redirectToRoute("frontend_building_index");
    }

    /**
     * @Route("/{building}/archive", name="frontend_building_archive")
     *
     * @param Building $building
     * @return Response
     */
    public function archiveAction(Building $building)
    {
        $building->setIsArchived(true);
        $this->fastSave($building);
        return $this->redirectToRoute("frontend_building_index");
    }

    /**
     * @Route("/{building}/un_publish", name="frontend_building_un_publish")
     *
     * @param Building $building
     * @return Response
     * @throws \Exception
     */
    public function unPublicAction(Building $building)
    {
        $building->unPublish();
        $this->fastSave($building);

        return $this->redirectToRoute("frontend_building_evaluate", ["building" => $building->getId()]);
    }

    /**
     * @Route("/{building}/notify", name="frontend_building_notify")
     *
     * @param Building $building
     * @param EmailServiceInterface $emailService
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function notifyAction(Building $building, EmailServiceInterface $emailService, TranslatorInterface $translator)
    {
        if (!$building->isAccessible()) {
            $building->publish();
            $this->fastSave($building);
        }

        /* @var Craftsman[] $toInform */
        $toInform = [];
        foreach ($building->getMarkers() as $marker) {
            if ($marker->getApproved() == null) {
                $toInform[$marker->getCraftsman()->getId()] = $marker->getCraftsman();
            }
        }

        foreach ($toInform as $craftsman) {
            $emailService->sendActionEmail(
                $craftsman->getEmail(),
                $translator->trans("notify.email.subject", ["%building_name%" => $building->getName()], "frontend_building"),
                $translator->trans("notify.email.body", ["%building_name%" => $building->getName(), "%name%" => $craftsman->getName()], "frontend_building"),
                $translator->trans("notify.email.action_text", [], "frontend_building"),
                $this->generateUrl("public_view_2", ["guid" => $building->getPublicIdentifier(), "guid2" => $craftsman->getId()])
            );
        }

        return $this->redirectToRoute("frontend_building_evaluate", ["building" => $building->getId()]);
    }

    /**
     * @Route("/{building}/evaluate", name="frontend_building_evaluate")
     *
     * @param Building $building
     * @return Response
     */
    public function evaluateAction(Building $building)
    {
        $arr["building"] = $building;

        $setMarkerInfo = function ($marker, $markerInfo) {
            /* @var Marker $marker */
            /* @var MarkerInfo $markerInfo */
            if ($marker->getApproved() instanceof \DateTime) {
                $markerInfo->setClosedMarkers($markerInfo->getClosedMarkers() + 1);
            } else {
                $markerInfo->setOpenMarkers($markerInfo->getOpenMarkers() + 1);
            }
        };

        $markers = $building->getMarkers();

        /* @var CraftsmanMarkerInfo[] $craftsmen */
        $craftsmen = [];
        foreach ($markers as $marker) {
            if (!isset($craftsmen[$marker->getCraftsman()->getId()])) {
                $model = new CraftsmanMarkerInfo();
                $model->setCraftsman($marker->getCraftsman());
                $craftsmen[$marker->getCraftsman()->getId()] = $model;
            }
            $craftsman = $craftsmen[$marker->getCraftsman()->getId()];
            $setMarkerInfo($marker, $craftsman);
        }
        $arr["craftsmen"] = $craftsmen;

        /* @var BuildingMapMarkerInfo[] $maps */
        $maps = [];
        foreach ($markers as $marker) {
            if (!isset($maps[$marker->getBuildingMap()->getId()])) {
                $model = new BuildingMapMarkerInfo();
                $model->setBuildingMap($marker->getBuildingMap());
                $maps[$marker->getBuildingMap()->getId()] = $model;
            }
            $craftsman = $maps[$marker->getBuildingMap()->getId()];
            $setMarkerInfo($marker, $craftsman);
        }
        $arr["maps"] = $maps;


        return $this->render('frontend/building/evaluate.html.twig', $arr);
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
