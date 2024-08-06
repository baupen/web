<?php

/*
 * This file is part of the baupen project.
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
use App\Helper\DoctrineHelper;
use App\Service\Email\EmailBodyGenerator;
use App\Service\Interfaces\EmailServiceInterface;
use App\Service\Report\Email\CraftsmanReport;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailService implements EmailServiceInterface
{
    private TranslatorInterface $translator;

    private LoggerInterface $logger;

    private RequestStack $requestStack;

    private UrlGeneratorInterface $urlGenerator;

    private ManagerRegistry $registry;

    private MailerInterface $mailer;

    private SerializerInterface $serializer;

    private EmailBodyGenerator $emailBodyGenerator;

    private string $mailerFromEmail;

    private string $baseUri;

    /**
     * EmailService constructor.
     */
    public function __construct(TranslatorInterface $translator, LoggerInterface $logger, RequestStack $request, UrlGeneratorInterface $urlGenerator, ManagerRegistry $registry, MailerInterface $mailer, SerializerInterface $serializer, string $mailerFromEmail, string $baseUri, EmailBodyGenerator $emailBodyGenerator)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->requestStack = $request;
        $this->urlGenerator = $urlGenerator;
        $this->registry = $registry;
        $this->mailer = $mailer;
        $this->serializer = $serializer;
        $this->mailerFromEmail = $mailerFromEmail;
        $this->emailBodyGenerator = $emailBodyGenerator;
        $this->baseUri = $baseUri;
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

    public function sendConstructionSitesReport(ConstructionManager $constructionManager, array $constructionSiteReports): bool
    {
        $emailBody = $this->emailBodyGenerator->fromConstructionSiteReports($constructionSiteReports);
        $json = $this->serializer->serialize($emailBody, 'json');
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

    public function sendCraftsmanIssueReminder(ConstructionManager $constructionManager, Craftsman $craftsman, CraftsmanReport $craftsmanReport, string $subject, string $body, bool $constructionManagerInBCC): bool
    {
        $report = $this->emailBodyGenerator->fromCraftsmanReport($craftsmanReport);
        $emailBody = ['report' => $report, 'body' => $body];
        $json = $this->serializer->serialize($emailBody, 'json');
        $link = $this->urlGenerator->generate('public_resolve', ['token' => $craftsman->getAuthenticationToken()]);
        $entity = Email::create(Email::TYPE_CRAFTSMAN_ISSUE_REMINDER, $constructionManager, $link, $json, true);

        $message = $this->createTemplatedEmailToCraftsman($constructionManager, $craftsman, $constructionManagerInBCC);
        if (!$message instanceof TemplatedEmail) {
            return false;
        }

        $message
            ->subject($subject)
            ->textTemplate('email/craftsman_issue_reminder.txt.twig')
            ->htmlTemplate('email/craftsman_issue_reminder.html.twig')
            ->context($entity->getContext());

        if (!$this->sendAndStoreEMail($message, $entity)) {
            return false;
        }

        $craftsman->setLastEmailReceived(new \DateTime());
        DoctrineHelper::persistAndFlush($this->registry, $craftsman);

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

    private function createTemplatedEmailToCraftsman(ConstructionManager $constructionManager, Craftsman $craftsman, bool $constructionManagerInBCC): ?TemplatedEmail
    {
        $templatedEmail = new TemplatedEmail();

        $from = new Address($this->mailerFromEmail, $constructionManager->getName());
        $constructionManagerAddress = new Address($constructionManager->getEmail(), $constructionManager->getName());

        $templatedEmail->from($from)
            ->to(new Address($craftsman->getEmail(), $craftsman->getContactName()))
            ->cc(...$craftsman->getEmailCCs())
            ->returnPath($constructionManagerAddress)
            ->replyTo($constructionManagerAddress);

        if ($constructionManagerInBCC) {
            $templatedEmail->bcc($constructionManagerAddress);
        }

        return $templatedEmail;
    }

    private function getCurrentPage(): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null !== $currentRequest) {
            return $currentRequest->getHttpHost();
        }

        return preg_replace('(^https?://)', '', $this->baseUri);
    }

    private function sendAndStoreEMail(TemplatedEmail $email, Email $entity): bool
    {
        try {
            $this->mailer->send($email);
            DoctrineHelper::persistAndFlush($this->registry, $entity);

            return true;
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('email send failed', ['exception' => $exception, 'email' => $entity]);

            return false;
        }
    }

    public static function tryConstructAddress(string $email, string $name = ''): ?Address
    {
        try {
            return new Address($email, $name);
        } catch (RfcComplianceException) {
            return null;
        }
    }
}
