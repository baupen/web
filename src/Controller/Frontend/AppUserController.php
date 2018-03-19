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
use App\Form\AppUser\AppUserType;
use function foo\func;
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
        $arr["app_user"] = $this->getDoctrine()->getRepository(AppUser::class)->findAll();
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
        return $this->handleAppUserForm(
            $request,
            new AppUser(),
            function ($arr) {
                return $this->render('frontend/app_user/new.html.twig', $arr);
            }
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
        return $this->handleAppUserForm(
            $request,
            $appUser,
            function ($arr) {
                return $this->render('frontend/app_user/edit.html.twig', $arr);
            }
        );
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
        $form = $this->handleRemoveForm(
            $request,
            $appUser,
            function () {
                return $this->generateUrl("frontend_app_user_index");
            }
        );
        $arr["form"] = $form->createView();
        $arr["app_user"] = $appUser;
        return $this->render('frontend/app_user/remove.html.twig', $arr);
    }

    /**
     * @param Request $request
     * @param AppUser $appUser
     * @param callable $render
     * @return Response
     */
    private function handleAppUserForm(Request $request, AppUser $appUser, callable $render)
    {

        $form = $this->handleForm(
            $this->createForm(AppUserType::class, $appUser),
            $request,
            function ($form) use ($appUser) {
                $appUser->setPassword();
                $appUser->setAuthenticationToken();
                $this->fastSave($appUser);

                return $this->redirectToRoute("frontend_app_user_index");
            }
        );
        if ($form instanceof Response)
            return $form;

        $arr["form"] = $form->createView();
        $arr["app_user"] = $appUser;
        return $render($arr);
    }
}
