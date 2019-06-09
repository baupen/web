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

use App\Controller\Base\BaseLoginController;
use App\Entity\ConstructionManager;
use App\Entity\Email;
use App\Enum\EmailType;
use App\Form\ConstructionManager\ConfirmType;
use App\Form\ConstructionManager\CreateType;
use App\Form\ConstructionManager\LoginType;
use App\Form\ConstructionManager\RecoverType;
use App\Form\ConstructionManager\SetPasswordType;
use App\Service\Interfaces\AuthorizationServiceInterface;
use App\Service\Interfaces\EmailServiceInterface;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/login")
 */
class LoginController extends BaseLoginController
{
    /**
     * @Route("", name="login")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        // relink if already logged in
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('dashboard');
        }

        $form = $this->createForm(LoginType::class);
        $form->add('submit', SubmitType::class, ['translation_domain' => 'login']);
        $this->handleLoginForm(
            $request,
            $form,
            function ($username) {
                return $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['email' => $username]);
            },
            new ConstructionManager()
        );

        return $this->render('login/login.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/create", name="login_create")
     *
     * @param Request                       $request
     * @param AuthorizationServiceInterface $authorizationService
     * @param TranslatorInterface           $translator
     * @param EmailServiceInterface         $emailService
     * @param LoggerInterface               $logger
     *
     * @throws Exception
     *
     * @return Response
     */
    public function createAction(Request $request, AuthorizationServiceInterface $authorizationService, TranslatorInterface $translator, EmailServiceInterface $emailService, LoggerInterface $logger)
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail($request->query->get('email'));

        $form = $this->createForm(CreateType::class, $constructionManager);
        $form->add('submit', SubmitType::class, ['translation_domain' => 'login']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$authorizationService->checkIfAuthorized($constructionManager->getEmail())) {
                $this->displayError($translator->trans('create.error.email_invalid', [], 'login'));
            } else {
                $response = $this->register($request, $constructionManager, $authorizationService, $translator, $emailService, $logger);
                if ($response instanceof Response) {
                    return $response;
                }
            }
        }

        return $this->render('login/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/confirm/{authenticationHash}", name="login_confirm")
     *
     * @param Request             $request
     * @param string              $authenticationHash
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function confirmAction(Request $request, string $authenticationHash, TranslatorInterface $translator)
    {
        $arr = [];

        /** @var ConstructionManager $user */
        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['authenticationHash' => $authenticationHash]);
        if ($user === null) {
            $this->displayError($translator->trans('confirm.error.invalid_hash', [], 'login'));

            return $this->redirectToRoute('login');
        }

        // relink to password forgot if already registered
        if ($user->isRegistrationCompleted()) {
            return $this->redirectToRoute('login_reset', ['authenticationHash' => $authenticationHash]);
        }

        $form = $this->handleForm(
            $this->createForm(ConfirmType::class, $user, ['data_class' => ConstructionManager::class])
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
                $user->setRegistrationCompleted();
                $user->setAuthenticationHash();
                $this->fastSave($user);

                //login user & redirect
                $this->loginUser($request, $user);

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
     * @Route("/recover", name="login_recover")
     *
     * @param Request               $request
     * @param EmailServiceInterface $emailService
     * @param TranslatorInterface   $translator
     * @param LoggerInterface       $logger
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
                if ($exitingUser === null) {
                    $logger->info('could not reset password of unknown user ' . $constructionManager->getEmail());
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

                    $logger->info('sent password reset email to ' . $email->getReceiver());
                    $this->displaySuccess($translator->trans('recover.success.email_sent', [], 'login'));
                } else {
                    $logger->error('could not send password reset email ' . $email->getId());
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
     * @Route("/reset/{authenticationHash}", name="login_reset")
     *
     * @param Request $request
     * @param $authenticationHash
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function resetAction(Request $request, $authenticationHash, TranslatorInterface $translator)
    {
        $arr = [];

        /** @var ConstructionManager $user */
        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['authenticationHash' => $authenticationHash]);
        if ($user === null) {
            $this->displayError($translator->trans('reset.error.invalid_hash', [], 'login'));

            return $this->redirectToRoute('login_recover');
        }

        // if registration incomplete; redirect to confirm page
        if (!$user->isRegistrationCompleted()) {
            return $this->redirect('login_confirm');
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

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheck()
    {
        throw new RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="login_logout")
     */
    public function logoutAction()
    {
        throw new RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }

    /**
     * @param Request                       $request
     * @param ConstructionManager           $constructionManager
     * @param AuthorizationServiceInterface $authorizationService
     * @param TranslatorInterface           $translator
     * @param EmailServiceInterface         $emailService
     * @param LoggerInterface               $logger
     *
     * @throws Exception
     *
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    private function register(Request $request, ConstructionManager $constructionManager, AuthorizationServiceInterface $authorizationService, TranslatorInterface $translator, EmailServiceInterface $emailService, LoggerInterface $logger)
    {
        /** @var ConstructionManager $existing */
        $existing = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['email' => $constructionManager->getEmail()]);
        if ($existing === null) {
            // prepare account for usage
            $constructionManager->setRegistrationDate();
            $constructionManager->setPlainPassword(uniqid('_initial_pw_'));
            $constructionManager->setPassword();
            $constructionManager->setAuthenticationHash();
            $constructionManager->setIsEnabled(true);

            $authorizationService->tryFillDefaultValues($constructionManager);

            $this->fastSave($constructionManager);
        } else {
            $constructionManager = $existing;
            if ($constructionManager->isRegistrationCompleted()) {
                $this->displayError($translator->trans('create.error.already_registered', [], 'login'));

                return $this->redirectToRoute('login');
            }
        }

        // construct email
        $email = new Email();
        $email->setEmailType(EmailType::ACTION_EMAIL);
        $email->setSystemSender();
        $email->setReceiver($constructionManager->getEmail());
        $email->setSubject($translator->trans('create.email.subject', ['%page%' => $request->getHttpHost()], 'login'));
        $email->setBody($translator->trans('create.email.body', [], 'login'));
        $email->setActionText($translator->trans('create.email.action_text', [], 'login'));
        $email->setActionLink($this->generateUrl('login_confirm', ['authenticationHash' => $constructionManager->getAuthenticationHash()], UrlGeneratorInterface::ABSOLUTE_URL));
        $this->fastSave($email);

        // send email
        if ($emailService->sendEmail($email)) {
            $email->setSentDateTime(new DateTime());
            $this->fastSave($email);

            $logger->info('sent register email to ' . $email->getReceiver());

            return $this->render('login/create_successful.html.twig');
        }
        $logger->error('could not send register email ' . $email->getId());
        $this->displayError($translator->trans('create.fail.welcome_email_not_sent', [], 'login'));

        return false;
    }
}
