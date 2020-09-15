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

use App\Controller\Base\BaseFormController;
use App\Entity\ConstructionManager;
use App\Entity\Email;
use App\Enum\EmailType;
use App\Form\RegisterType;
use App\Security\LoginFormAuthenticator;
use App\Service\Interfaces\AuthorizationServiceInterface;
use App\Service\Interfaces\EmailServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends BaseFormController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/register", name="register")
     *
     * @return Response
     */
    public function registerAction(Request $request, AuthorizationServiceInterface $authorizationService, TranslatorInterface $translator, EmailServiceInterface $emailService, LoggerInterface $logger)
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail($request->query->get('email'));

        $form = $this->createForm(RegisterType::class, $constructionManager);
        $form->add('submit', SubmitType::class, ['translation_domain' => 'login']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $authorizationService->setIsEnabled($constructionManager);

            if (!$constructionManager->getIsEnabled()) {
                $this->displayError($translator->trans('create.error.email_invalid', [], 'login'));
            } else {
                /** @var ConstructionManager $existing */
                $existing = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['email' => $constructionManager->getEmail()]);

                if (null !== $existing && $existing->getRegistrationCompleted()) {
                    $this->displayError($translator->trans('create.error.already_registered', [], 'login'));

                    return $this->redirectToRoute('login');
                }

                if (null !== $existing) {
                    $constructionManager = $existing;
                }

                if ($emailService->sendRegisterConfirm($constructionManager)) {
                    $this->displayError($translator->trans('register.success.welcome', [], 'security'));
                } else {
                    $this->displayError($translator->trans('register.fail.welcome_email_not_sent', [], 'security'));
                }

                $this->fastSave($constructionManager);
            }
        }

        return $this->render('security/register.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/register/confirm/{authenticationHash}", name="register_confirm")
     *
     * @return Response
     */
    public function registerConfirmAction(Request $request, string $authenticationHash, TranslatorInterface $translator, EmailServiceInterface $emailService)
    {
        $arr = [];

        /** @var ConstructionManager $user */
        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['authenticationHash' => $authenticationHash]);
        if (null === $user) {
            $this->displayError($translator->trans('confirm.error.invalid_hash', [], 'login'));

            return $this->redirectToRoute('login');
        }

        // relink to password forgot if already registered
        if ($user->isRegistrationCompleted()) {
            return $this->redirectToRoute('login_reset', ['authenticationHash' => $authenticationHash]);
        }

        $form = $this->handleForm(
            $this->createForm(ConfirmType::class, $user, ['data_class' => ConstructionManager::class])
                ->add('submit', SubmitType::class, ['translation_domain' => 'login', 'label' => 'login.submit']),
            $request,
            function ($form) use ($user, $translator, $request, $emailService) {
                //check for valid password
                if ($user->getPlainPassword() !== $user->getRepeatPlainPassword()) {
                    $this->displayError($translator->trans('reset.error.passwords_do_not_match', [], 'login'));

                    return $form;
                }

                //display success
                $this->displaySuccess($translator->trans('reset.success.password_set', [], 'login'));

                //set new password & save
                $user->setPassword();
                $user->setRegistrationCompleted();
                $user->setAuthenticationHash();
                $this->fastSave($user);

                //login user & redirect
                $this->loginUser($request, $user);

                if ($user->getIsExternalAccount()) {
                    $this->sendAppEMail($request, $user, $translator, $emailService);
                }

                return $this->redirectToRoute('help_overview');
            }
        );

        if ($form instanceof Response) {
            return $form;
        }

        $arr['form'] = $form->createView();

        return $this->render('login/confirm.html.twig', $arr);
    }

    /**
     * @Route("/recover", name="recover")
     *
     * @return Response
     */
    public function recoverAction(Request $request, EmailServiceInterface $emailService, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $form = $this->handleForm(
            $this->createForm(RecoverType::class)
                ->add('submit', SubmitType::class, ['translation_domain' => 'login']),
            $request,
            function ($form) use ($emailService, $translator, $logger, $request) {
                /** @var FormInterface $form */
                /** @var ConstructionManager $constructionManager */
                $constructionManager = $form->getData();
                //check if user exists
                /** @var ConstructionManager $exitingUser */
                $exitingUser = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['email' => $constructionManager->getEmail()]);
                if (null === $exitingUser) {
                    $logger->info('could not reset password of unknown user '.$constructionManager->getEmail());
                    $this->displayError($translator->trans('recover.fail.email_not_found', [], 'login'));

                    return $form;
                }

                //create new reset hash
                $exitingUser->setAuthenticationHash();
                $this->fastSave($exitingUser);

                //create email
                $email = new Email();
                $email->setEmailType(EmailType::ACTION_EMAIL);
                $email->setSystemSender();
                $email->setReceiver($exitingUser->getEmail());
                $email->setSubject($translator->trans('recover.email.reset_password.subject', ['%page%' => $request->getHttpHost()], 'login'));
                $email->setBody($translator->trans('recover.email.reset_password.message', [], 'login'));
                $email->setActionText($translator->trans('recover.email.reset_password.action_text', [], 'login'));
                $email->setActionLink($this->generateUrl('login_reset', ['authenticationHash' => $exitingUser->getAuthenticationHash()], UrlGeneratorInterface::ABSOLUTE_URL));

                //save & send
                $this->fastSave($email);
                if ($emailService->sendEmail($email)) {
                    $email->setSentDateTime(new DateTime());
                    $this->fastSave($email);

                    $logger->info('sent password reset email to '.$email->getReceiver());
                    $this->displaySuccess($translator->trans('recover.success.email_sent', [], 'login'));
                } else {
                    $logger->error('could not send password reset email '.$email->getId());
                    $this->displayError($translator->trans('recover.fail.email_not_sent', [], 'login'));
                }

                return $form;
            }
        );

        $arr = [];
        $arr['form'] = $form->createView();

        return $this->render('login/recover.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/recover/confirm/{authenticationHash}", name="recover_confirm")
     *
     * @param $authenticationHash
     *
     * @return Response
     */
    public function resetAction(Request $request, $authenticationHash, TranslatorInterface $translator)
    {
        $arr = [];

        /** @var ConstructionManager $user */
        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['authenticationHash' => $authenticationHash]);
        if (null === $user) {
            $this->displayError($translator->trans('reset.error.invalid_hash', [], 'login'));

            return $this->redirectToRoute('login_recover');
        }

        // if registration incomplete; redirect to confirm page
        if (!$user->isRegistrationCompleted()) {
            return $this->redirectToRoute('login_confirm', ['authenticationHash' => $authenticationHash]);
        }

        $form = $this->handleForm(
            $this->createForm(SetPasswordType::class, $user, ['data_class' => ConstructionManager::class])
                ->add('submit', SubmitType::class, ['translation_domain' => 'login']),
            $request,
            function ($form) use ($user, $translator, $request) {
                //check for valid password
                if ($user->getPlainPassword() !== $user->getRepeatPlainPassword()) {
                    $this->displayError($translator->trans('reset.error.passwords_do_not_match', [], 'login'));

                    return $form;
                }

                //display success
                $this->displaySuccess($translator->trans('reset.success.password_set', [], 'login'));

                //set new password & save
                $user->setPassword();
                $user->setAuthenticationHash();
                $this->fastSave($user);

                //login user & redirect
                $this->loginUser($request, $user);

                return $this->redirectToRoute('dashboard');
            }
        );

        if ($form instanceof Response) {
            return $form;
        }

        $arr['form'] = $form->createView();

        return $this->render('login/reset.html.twig', $arr);
    }

    private function sendAppEMail(Request $request, ConstructionManager $user, TranslatorInterface $translator, EmailServiceInterface $emailService)
    {
        $email = new Email();
        $email->setReceiver($user->getEmail());

        $email->setEmailType(EmailType::ACTION_EMAIL);
        $email->setSubject($translator->trans('confirm.app_email.subject', [], 'login'));
        $email->setBody($translator->trans('confirm.app_email.body', ['%website%' => $request->getHttpHost()], 'login'));
        $email->setActionText($translator->trans('confirm.app_email.action_text', [], 'login'));
        $email->setActionLink('mangel.io://login?username='.urlencode($user->getEmail()).'&domain='.urlencode($request->getHttpHost()));

        $this->fastSave($email);

        // send email
        if ($emailService->sendEmail($email)) {
            $email->setSentDateTime(new \DateTime());
            $this->fastSave($email);
        }
    }

    private function loginUser(UserInterface $user, LoginFormAuthenticator $authenticator, GuardAuthenticatorHandler $guardHandler, Request $request)
    {
        // after validating the user and saving them to the database
        // authenticate the user and use onAuthenticationSuccess on the authenticator
        return $guardHandler->authenticateUserAndHandleSuccess(
            $user,          // the User object you just created
            $request,
            $authenticator, // authenticator whose onAuthenticationSuccess you want to use
            'main'          // the name of your firewall in security.yaml
        );
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
