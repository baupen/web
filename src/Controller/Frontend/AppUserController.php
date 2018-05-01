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
use App\Entity\AppUser;
use App\Model\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/app_user")
 * @Security("has_role('ROLE_FRONTEND_USER')")
 *
 * @return Response
 */
class AppUserController extends BaseFrontendController
{
    /**
     * @Route("/", name="frontend_app_user_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $arr["app_users"] = $this->getDoctrine()->getRepository(AppUser::class)->findAll();
        return $this->render('frontend/app_user/index.html.twig', $arr);
    }


    /**
     * @Route("/new", name="frontend_app_user_new")
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $appUser = new AppUser();
        $form = $this->handleCreateForm(
            $request,
            $appUser,
            function () use ($appUser) {
                /* @var AppUser $appUser */

                //look for existing
                $existing = $this->getDoctrine()->getRepository(AppUser::class)->findOneBy(["identifier" => $appUser->getIdentifier()]);
                if ($existing !== null) {
                    $this->displayError($this->getTranslator()->trans("new.error.identifier_already_exists", [], "frontend_app_user"));
                    return false;
                }

                $appUser->setPassword();
                $appUser->setAuthenticationToken();
                return true;
            }
        );
        if ($form instanceof Response)
            return $form;

        $arr["form"] = $form->createView();
        return $this->render(
            'frontend/app_user/new.html.twig',
            $arr
        );
    }

    /**
     * @Route("/{appUser}/edit", name="frontend_app_user_edit")
     *
     * @param Request $request
     * @param AppUser $appUser
     * @return Response
     */
    public function editAction(Request $request, AppUser $appUser)
    {
        $form = $this->handleUpdateForm(
            $request,
            $appUser,
            function () use ($appUser) {
                /* @var AppUser $appUser */

                //look for existing
                $existing = $this->getDoctrine()->getRepository(AppUser::class)->findBy(["identifier" => $appUser->getIdentifier()]);
                if (count($existing) > 1 || (count($existing) == 1 && $existing[0]->getId() != $appUser->getId())) {
                    $this->displayError($this->getTranslator()->trans("new.error.identifier_already_exists", [], "frontend_app_user"));
                    return false;
                }

                if ($appUser->getPlainPassword() != "") {
                    $this->displaySuccess($this->getTranslator()->trans("edit.success.password_changed", [], "frontend_app_user"));
                    $appUser->setPassword();
                    $appUser->setAuthenticationToken();
                }

                return true;
            }
        );
        if ($form instanceof Response)
            return $form;

        $arr["form"] = $form->createView();
        $arr["app_user"] = $appUser;
        return $this->render('frontend/app_user/edit.html.twig', $arr);

    }

    /**
     * @Route("/{appUser}/remove", name="frontend_app_user_remove")
     *
     * @param Request $request
     * @param AppUser $appUser
     * @return Response
     */
    public function removeAction(Request $request, AppUser $appUser)
    {
        $removed = false;
        $form = $this->handleRemoveForm(
            $request,
            $appUser,
            function () use (&$removed) {
                $removed = true;
            }
        );
        if ($removed)
            return $this->redirectToRoute("frontend_app_user_index");

        $arr["form"] = $form->createView();
        $arr["app_user"] = $appUser;
        return $this->render('frontend/app_user/remove.html.twig', $arr);
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
                $this->generateUrl("frontend_app_user_index"),
                $translator->trans("index.title", [], "frontend_app_user")
            )
        ];
    }
}
