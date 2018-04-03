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
use App\Entity\Craftsman;
use App\Model\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/craftsman")
 * @Security("has_role('ROLE_FRONTEND_USER')")
 *
 * @return Response
 */
class CraftsmanController extends BaseFrontendController
{
    /**
     * @Route("/", name="frontend_craftsman_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $arr["craftsmen"] = $this->getDoctrine()->getRepository(Craftsman::class)->findAll();
        return $this->render('frontend/craftsman/index.html.twig', $arr);
    }


    /**
     * @Route("/new", name="frontend_craftsman_new")
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $craftsman = new Craftsman();
        $form = $this->handleCreateForm(
            $request,
            $craftsman
        );
        if ($form instanceof Response)
            return $form;

        $arr["form"] = $form->createView();
        return $this->render(
            'frontend/craftsman/new.html.twig',
            $arr
        );
    }

    /**
     * @Route("/{craftsman}/edit", name="frontend_craftsman_edit")
     *
     * @param Request $request
     * @param Craftsman $craftsman
     * @return Response
     */
    public function editAction(Request $request, Craftsman $craftsman)
    {
        $form = $this->handleUpdateForm(
            $request,
            $craftsman
        );
        if ($form instanceof Response)
            return $form;

        $arr["form"] = $form->createView();
        $arr["craftsman"] = $craftsman;
        return $this->render('frontend/craftsman/edit.html.twig', $arr);

    }

    /**
     * @Route("/{craftsman}/remove", name="frontend_craftsman_remove")
     *
     * @param Request $request
     * @param Craftsman $craftsman
     * @return Response
     */
    public function removeAction(Request $request, Craftsman $craftsman)
    {
        $removed = false;
        $form = $this->handleRemoveForm(
            $request,
            $craftsman,
            function () use (&$removed) {
                $removed = true;
            }
        );
        if ($removed)
            return $this->redirectToRoute("frontend_craftsman_index");

        $arr["form"] = $form->createView();
        $arr["craftsman"] = $craftsman;
        return $this->render('frontend/craftsman/remove.html.twig', $arr);
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
                $this->generateUrl("frontend_craftsman_index"),
                $translator->trans("index.title", [], "frontend_craftsman")
            )
        ];
    }
}
