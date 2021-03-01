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
use App\Form\ConstructionManager\RegisterConfirmType;
use App\Form\UserTrait\LoginType;
use App\Form\UserTrait\OnlyEmailType;
use App\Form\UserTrait\SetPasswordType;
use App\Security\Exceptions\UserWithoutPasswordAuthenticationException;
use App\Security\LoginFormAuthenticator;
use App\Service\Interfaces\EmailServiceInterface;
use App\Service\Interfaces\SampleServiceInterface;
use App\Service\Interfaces\UserServiceInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends BaseFormController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils, EmailServiceInterface $emailService, LoggerInterface $logger): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }

        // show last auth error
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error instanceof DisabledException) {
            $this->displayError($this->getTranslator()->trans('login.errors.account_disabled', [], 'security'));
        } elseif ($error instanceof BadCredentialsException) {
            $this->displayError($this->getTranslator()->trans('login.errors.password_wrong', [], 'security'));
        } elseif ($error instanceof UsernameNotFoundException) {
            $this->displayError($this->getTranslator()->trans('login.errors.email_not_found', [], 'security'));
        } elseif ($error instanceof UserWithoutPasswordAuthenticationException) {
            $this->displayError($this->getTranslator()->trans('login.errors.registration_not_completed', [], 'security'));
            $emailService->sendRegisterConfirmLink($error->getUser());
        } elseif (null !== $error) {
            $this->displayError($this->getTranslator()->trans('login.errors.login_failed', [], 'security'));
            $logger->error('login failed', ['exception' => $error]);
        }

        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail($authenticationUtils->getLastUsername());

        $form = $this->createForm(LoginType::class, $constructionManager);
        $form->add('submit', SubmitType::class, ['translation_domain' => 'security', 'label' => 'login.submit']);

        return $this->render('security/login.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/token", name="token")
     */
    public function token(): Response
    {
        $token = $this->getUser()->getAuthenticationToken();

        return new Response($token);
    }

    /**
     * @Route("/register", name="register")
     *
     * @return Response
     */
    public function registerAction(Request $request, TranslatorInterface $translator, UserServiceInterface $userService)
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail($request->query->get('email'));

        $form = $this->createForm(OnlyEmailType::class, $constructionManager);
        $form->add('submit', SubmitType::class, ['translation_domain' => 'security', 'label' => 'register.title']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($userService->tryRegister($constructionManager, $error)) {
                $message = $translator->trans('register.success.welcome', [], 'security');
                $this->displaySuccess($message);

                return $this->redirectToRoute('login');
            }

            $message = $translator->trans('register.error.unknown', [], 'security');
            switch ($error) {
                case UserServiceInterface::REGISTRATION_FAIL_ACCOUNT_DISABLED:
                    $message = $translator->trans('register.error.account_disabled', [], 'security');
                    break;
                case UserServiceInterface::REGISTRATION_FAIL_ALREADY_REGISTERED:
                    $message = $translator->trans('register.error.already_registered', [], 'security');
                    break;
                case UserServiceInterface::REGISTRATION_FAIL_EMAIL_NOT_SENT:
                    $message = $translator->trans('register.error.welcome_email_not_sent', [], 'security');
                    break;
            }

            $this->displayError($message);
        }

        return $this->render('security/register.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/register/confirm/{authenticationHash}", name="register_confirm")
     *
     * @return Response
     */
    public function registerConfirmAction(Request $request, string $authenticationHash, TranslatorInterface $translator, EmailServiceInterface $emailService, SampleServiceInterface $sampleService, UserServiceInterface $userService, LoginFormAuthenticator $authenticator, GuardAuthenticatorHandler $guardHandler)
    {
        /** @var ConstructionManager $constructionManager */
        if (!$this->getConstructionManagerFromAuthenticationHash($authenticationHash, $translator, $constructionManager)) {
            return $this->redirectToRoute('login');
        }

        if ($constructionManager->getRegistrationCompleted()) {
            $this->displayError($translator->trans('register.error.already_registered', [], 'security'));

            return $this->redirectToRoute('login');
        }

        $userService->setDefaultValues($constructionManager);
        $userService->authorize($constructionManager);

        $form = $this->createForm(RegisterConfirmType::class, $constructionManager);
        $form->add('submit', SubmitType::class, ['translation_domain' => 'security', 'label' => 'register_confirm.submit']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->applySetPasswordType($form->get('password'), $constructionManager, $translator)) {
            $constructionManager->setAuthenticationToken();
            $this->fastSave($constructionManager);

            if (!$constructionManager->getCanAssociateSelf() && 0 === count($constructionManager->getConstructionSites())) {
                $sampleService->createSampleConstructionSite(SampleServiceInterface::SAMPLE_SIMPLE, $constructionManager);
            }

            $this->loginUser($constructionManager, $authenticator, $guardHandler, $request);
            $this->displaySuccess($translator->trans('register_confirm.success.welcome', [], 'security'));
            $emailService->sendAppInvitation($constructionManager);

            return $this->redirectToRoute('help_welcome');
        }

        return $this->render('security/register_confirm.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/recover", name="recover")
     *
     * @return Response
     */
    public function recoverAction(Request $request, EmailServiceInterface $emailService, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $constructionManager = new ConstructionManager();
        $form = $this->createForm(OnlyEmailType::class, $constructionManager);
        $form->add('submit', SubmitType::class, ['translation_domain' => 'security', 'label' => 'recover.submit']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ConstructionManager $existingConstructionManager */
            $existingConstructionManager = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['email' => $constructionManager->getEmail()]);
            if (null === $existingConstructionManager) {
                $logger->info('could not reset password of unknown user '.$constructionManager->getEmail());
                $this->displayError($translator->trans('recover.fail.email_not_found', [], 'security'));
            } else {
                $this->sendAuthenticationLink($existingConstructionManager, $emailService, $logger, $translator);
            }
        }

        return $this->render('security/recover.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/recover/confirm/{authenticationHash}", name="recover_confirm")
     *
     * @param $authenticationHash
     *
     * @return Response
     */
    public function recoverConfirmAction(Request $request, $authenticationHash, TranslatorInterface $translator, LoginFormAuthenticator $authenticator, GuardAuthenticatorHandler $guardHandler)
    {
        /** @var ConstructionManager $constructionManager */
        if (!$this->getConstructionManagerFromAuthenticationHash($authenticationHash, $translator, $constructionManager)) {
            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(SetPasswordType::class, $constructionManager);
        $form->add('submit', SubmitType::class, ['translation_domain' => 'security', 'label' => 'recover_confirm.submit']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->applySetPasswordType($form, $constructionManager, $translator)) {
            $constructionManager->setAuthenticationHash();
            $constructionManager->setAuthenticationToken();
            $this->fastSave($constructionManager);

            $message = $translator->trans('recover_confirm.success.password_set', [], 'security');
            $this->displaySuccess($message);

            $this->loginUser($constructionManager, $authenticator, $guardHandler, $request);

            return $this->redirectToRoute('index');
        }

        return $this->render('security/recover_confirm.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function getConstructionManagerFromAuthenticationHash(string $authenticationHash, TranslatorInterface $translator, ConstructionManager &$constructionManager = null)
    {
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['authenticationHash' => $authenticationHash]);
        if (null === $constructionManager) {
            $this->displayError($translator->trans('recover_confirm.error.invalid_hash', [], 'security'));

            return false;
        }

        return true;
    }

    private function applySetPasswordType(FormInterface $form, ConstructionManager $constructionManager, TranslatorInterface $translator)
    {
        $plainPassword = $form->get('plainPassword')->getData();
        $repeatPlainPassword = $form->get('repeatPlainPassword')->getData();

        if (strlen($plainPassword) < 8) {
            $this->displayError($translator->trans('recover_confirm.error.password_too_short', [], 'security'));

            return false;
        }

        if ($plainPassword !== $repeatPlainPassword) {
            $this->displayError($translator->trans('recover_confirm.error.passwords_do_not_match', [], 'security'));

            return false;
        }

        $constructionManager->setPasswordFromPlain($plainPassword);

        return true;
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

    private function sendAuthenticationLink(ConstructionManager $existingConstructionManager, EmailServiceInterface $emailService, LoggerInterface $logger, TranslatorInterface $translator): void
    {
        $existingConstructionManager->setAuthenticationHash();
        $this->fastSave($existingConstructionManager);

        if ($emailService->sendRecoverConfirmLink($existingConstructionManager)) {
            $logger->info('sent password reset email to '.$existingConstructionManager->getEmail());
            $this->displaySuccess($translator->trans('recover.success.email_sent', [], 'security'));
        } else {
            $logger->error('could not send password reset email '.$existingConstructionManager->getEmail());
            $this->displayError($translator->trans('recover.fail.email_not_sent', [], 'security'));
        }
    }
}
