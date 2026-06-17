<?php

namespace App\Controller;

use App\Entity\ConstructionManager;
use App\Form\ConstructionManager\EditProfileType;
use App\Form\ConstructionManager\RegisterConfirmType;
use App\Form\ConstructionManager\SettingsType;
use App\Form\UserTrait\SetPasswordType;
use App\Helper\DoctrineHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/profile')]
class ProfileController extends AbstractController
{
    #[Route(path: '', name: 'profile')]
    public function profile(Request $request, TranslatorInterface $translator, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $registry): Response
    {
        $constructionManager = $this->getUser();
        /** @var ConstructionManager $constructionManager */

        $profileForm = $this->createForm(EditProfileType::class, $constructionManager);
        $profileForm->add('submit', SubmitType::class, ['translation_domain' => 'profile', 'label' => 'index.edit_profile_submit']);

        $setPasswordForm = $this->createForm(SetPasswordType::class, $constructionManager);
        $setPasswordForm->add('submit', SubmitType::class, ['translation_domain' => 'profile', 'label' => 'index.set_password_submit']);

        $settingsForm = $this->createForm(SettingsType::class, $constructionManager);
        $settingsForm->add('submit', SubmitType::class, ['translation_domain' => 'profile', 'label' => 'index.settings_submit']);

        $profileForm->handleRequest($request);
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            DoctrineHelper::persistAndFlush($registry, $constructionManager);

            $this->addFlash('success', $translator->trans('index.success.profile_updated', [], 'profile'));
        }

        $setPasswordForm->handleRequest($request);
        $error = null;
        if ($setPasswordForm->isSubmitted() && $setPasswordForm->isValid() && SecurityController::applySetPasswordType($setPasswordForm, $constructionManager, $translator, $passwordHasher, $error)) {
            $constructionManager->setAuthenticationToken();
            DoctrineHelper::persistAndFlush($registry, $constructionManager);

            $this->addFlash('success', $translator->trans('index.success.password_updated', [], 'profile'));
        }

        if ($error) {
            $this->addFlash('danger', $error);
        }

        $settingsForm->handleRequest($request);
        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            DoctrineHelper::persistAndFlush($registry, $constructionManager);

            $this->addFlash('success', $translator->trans('index.success.settings_updated', [], 'profile'));
        }


        return $this->render('profile/index.html.twig', ['profile_form' => $profileForm->createView(), 'set_password_form' => $setPasswordForm->createView(), 'settings_form' => $settingsForm->createView()]);
    }
}
