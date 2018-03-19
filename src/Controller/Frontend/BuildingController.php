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
use App\Model\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $arr["buildings"] = $this->getDoctrine()->getRepository(Building::class)->findAll();
        return $this->render('frontend/building/index.html.twig', $arr);
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
     * @param Request $request
     * @param Building $building
     * @return Response
     */
    public function viewAction(Request $request, Building $building)
    {
        $arr["building"] = $building;
        return $this->render('frontend/building/view.html.twig', $arr);
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
