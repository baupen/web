<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionManager;
use App\Entity\Email;
use App\Enum\EmailType;
use App\Service\Email\SendService;
use App\Service\Interfaces\EmailServiceInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class EmailService implements EmailServiceInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var SendService
     */
    private $sendService;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function renderEmail(Email $email)
    {
        return $this->twig->render('email/content.html.twig', ['email' => $email]);
    }

    /**
     * @return false
     *
     * @throws Exception
     */
    public function sendRegisterConfirm(ConstructionManager $constructionManager)
    {
        $constructionManager->generateAuthenticationHash();

        // construct email
        $email = new Email();
        $email->setEmailType(EmailType::ACTION_EMAIL);
        $email->setReceiver($constructionManager->getEmail());
        $email->setSubject($this->translator->trans('create.email.subject', ['%page%' => $this->request->getCurrentRequest()->getHttpHost()], 'login'));
        $email->setBody($this->translator->trans('create.email.body', [], 'login'));
        $email->setActionText($this->translator->trans('create.email.action_text', [], 'login'));
        $email->setActionLink($this->urlGenerator->generate('register_confirm', ['authenticationHash' => $constructionManager->getAuthenticationHash()], UrlGeneratorInterface::ABSOLUTE_URL));

        // send
        $html = $this->renderEmail($email);
        if ($this->sendService->sendEmail($email, $html)) {
            $email->confirmSent();

            $this->manager->persist($constructionManager);
            $this->manager->persist($email);
            $this->manager->flush();
        }

        return false;
    }
}
