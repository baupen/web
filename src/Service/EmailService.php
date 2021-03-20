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
use App\Entity\Craftsman;
use App\Entity\Email;
use App\Service\Interfaces\EmailServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailService implements EmailServiceInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $mailerFromEmail;

    /**
     * @var string
     */
    private $supportEmail;

    /**
     * EmailService constructor.
     */
    public function __construct(TranslatorInterface $translator, LoggerInterface $logger, RequestStack $request, UrlGeneratorInterface $urlGenerator, ManagerRegistry $registry, MailerInterface $mailer, SerializerInterface $serializer, string $mailerFromEmail, string $supportEmail)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->request = $request;
        $this->urlGenerator = $urlGenerator;
        $this->manager = $registry->getManager();
        $this->mailer = $mailer;
        $this->serializer = $serializer;
        $this->mailerFromEmail = $mailerFromEmail;
        $this->supportEmail = $supportEmail;
    }

    public function sendRegisterConfirmLink(ConstructionManager $constructionManager): bool
    {
        $link = $this->urlGenerator->generate('register_confirm', ['authenticationHash' => $constructionManager->getAuthenticationHash()]);
        $entity = Email::create(Email::TYPE_REGISTER_CONFIRM, $constructionManager, $link);
        $subject = $this->translator->trans('register_confirm.subject', ['%page%' => $this->getCurrentPage()], 'email');

        $message = $this->createTemplatedEmailToConstructionManager($constructionManager)
            ->subject($subject)
            ->textTemplate('email/register_confirm.txt.twig')
            ->htmlTemplate('email/register_confirm.html.twig')
            ->context($entity->getContext());

        return $this->sendAndStoreEMail($message, $entity);
    }

    public function sendConstructionSitesOverview(ConstructionManager $constructionManager, array $tableWithLinks): bool
    {
        $json = $this->serializer->serialize($tableWithLinks, 'json');
        $entity = Email::create(Email::TYPE_CONSTRUCTION_SITES_OVERVIEW, $constructionManager, null, $json, true);
        $subject = $this->translator->trans('construction_sites_overview.subject', ['%page%' => $this->getCurrentPage()], 'email');

        $message = $this->createTemplatedEmailToConstructionManager($constructionManager)
            ->subject($subject)
            ->textTemplate('email/construction_sites_overview.txt.twig')
            ->htmlTemplate('email/construction_sites_overview.html.twig')
            ->context($entity->getContext());

        return $this->sendAndStoreEMail($message, $entity);
    }

    public function sendRecoverConfirmLink(ConstructionManager $constructionManager): bool
    {
        $link = $this->urlGenerator->generate('recover_confirm', ['authenticationHash' => $constructionManager->getAuthenticationHash()]);
        $entity = Email::create(Email::TYPE_RECOVER_CONFIRM, $constructionManager, $link);
        $subject = $this->translator->trans('recover_confirm.subject', ['%page%' => $this->getCurrentPage()], 'email');

        $message = $this->createTemplatedEmailToConstructionManager($constructionManager)
            ->subject($subject)
            ->textTemplate('email/recover_confirm.txt.twig')
            ->htmlTemplate('email/recover_confirm.html.twig')
            ->context($entity->getContext());

        return $this->sendAndStoreEMail($message, $entity);
    }

    public function sendAppInvitation(ConstructionManager $constructionManager): bool
    {
        $entity = Email::create(Email::TYPE_APP_INVITATION, $constructionManager);
        $subject = $this->translator->trans('app_invitation.subject', ['%page%' => $this->getCurrentPage()], 'email');

        $message = $this->createTemplatedEmailToConstructionManager($constructionManager)
            ->subject($subject)
            ->textTemplate('email/app_invitation.txt.twig')
            ->htmlTemplate('email/app_invitation.html.twig')
            ->context($entity->getContext());

        return $this->sendAndStoreEMail($message, $entity);
    }

    public function sendCraftsmanIssueReminder(ConstructionManager $constructionManager, Craftsman $craftsman, string $subject, string $body, bool $constructionManagerInBCC): bool
    {
        $link = $this->urlGenerator->generate('public_resolve', ['token' => $craftsman->getAuthenticationToken()]);
        $entity = Email::create(Email::TYPE_CRAFTSMAN_ISSUE_REMINDER, $constructionManager, $link, $body);

        $message = $this->createTemplatedEmailToCraftsman($constructionManager, $craftsman, $constructionManagerInBCC)
            ->subject($subject)
            ->textTemplate('email/craftsman_issue_reminder.txt.twig')
            ->htmlTemplate('email/craftsman_issue_reminder.html.twig')
            ->context($entity->getContext());

        if (!$this->sendAndStoreEMail($message, $entity)) {
            return false;
        }

        $craftsman->setLastEmailReceived(new \DateTime());
        $this->manager->persist($craftsman);
        $this->manager->flush();

        return true;
    }

    private function createTemplatedEmailToConstructionManager(ConstructionManager $constructionManager): TemplatedEmail
    {
        $templatedEmail = new TemplatedEmail();

        $templatedEmail->from($this->mailerFromEmail)
            ->to(new Address($constructionManager->getEmail(), $constructionManager->getName()))
            ->replyTo($this->mailerFromEmail);

        return $templatedEmail;
    }

    private function createTemplatedEmailToCraftsman(ConstructionManager $constructionManager, Craftsman $craftsman, bool $constructionManagerInBCC): TemplatedEmail
    {
        $templatedEmail = new TemplatedEmail();

        $constructionManagerAddress = new Address($constructionManager->getEmail(), $constructionManager->getName());
        $templatedEmail->from($this->mailerFromEmail)
            ->to(new Address($craftsman->getEmail(), $craftsman->getContactName()))
            ->cc(...$craftsman->getEmailCCs())
            ->returnPath($constructionManagerAddress)
            ->replyTo($constructionManagerAddress);

        if ($constructionManagerInBCC) {
            $templatedEmail->bcc($constructionManagerAddress);
        }

        return $templatedEmail;
    }

    private function getCurrentPage()
    {
        return $this->request->getCurrentRequest() ? $this->request->getCurrentRequest()->getHttpHost() : 'localhost';
    }

    private function sendAndStoreEMail(TemplatedEmail $email, Email $entity): bool
    {
        try {
            $this->mailer->send($email);

            $this->manager->persist($entity);
            $this->manager->flush();

            return true;
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('email send failed', ['exception' => $exception, 'email' => $entity]);

            return false;
        }
    }
}
