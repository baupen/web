<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/02/2018
 * Time: 11:35
 */

namespace App\Controller\Frontend;

use App\Controller\Base\BaseLoginController;
use App\Entity\FrontendUser;
use App\Form\FrontendUser\FrontendUserType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/register")
 */
class RegisterController extends BaseLoginController
{
    /**
     * @Route("/", name="frontend_register_index")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function registerAction(Request $request, TranslatorInterface $translator)
    {
        $user = new FrontendUser();

        $form = $this->handleForm(
            $this->createForm(FrontendUserType::class)
                ->add("form.register", SubmitType::class),
            $request,
            function ($form) use ($request, $user, $translator) {
                //check for matching passwords
                if ($user->getPlainPassword() !== $user->getRepeatPlainPassword()) {
                    $this->displayError($translator->trans("index.error.passwords_do_not_match", [], "frontend_register"));
                    return $form;
                }

                //persist user
                $user->setRegistrationDate(new \DateTime());
                $user->setPassword();
                $user->setResetHash();
                $this->fastSave($user);

                //login & redirect
                $this->loginUser($request, $user);
                return $this->redirectToRoute("frontend_dashboard_index");
            }
        );

        if ($form instanceof Response) {
            return $form;
        }

        $arr["form"] = $form->createView();
        return $this->render('frontend/register/index.html.twig', $arr);
    }
}
