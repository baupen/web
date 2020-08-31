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

use App\Entity\Email;
use App\Enum\EmailType;
use App\Service\Interfaces\EmailServiceInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class EmailService implements EmailServiceInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var string
     */
    private $mailerFromEmail;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * EmailService constructor.
     */
    public function __construct(MailerInterface $mailer, LoggerInterface $logger, Environment $twig, string $mailerFromEmail)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->mailerFromEmail = $mailerFromEmail;
    }

    /**
     * @param string[] $options
     *
     * @return bool
     */
    public function sendEmail(Email $email)
    {
        $message = (new \Symfony\Component\Mime\Email())
            ->subject($email->getSubject())
            ->from($this->mailerFromEmail)
            ->to($email->getReceiver());

        //set reply to
        if ($email->getSenderEmail()) {
            $message->addReplyTo($email->getSenderEmail());
        } else {
            $message->addReplyTo($this->mailerFromEmail);
        }

        //construct plain body
        $bodyText = $email->getBody();
        if (null !== $email->getActionLink()) {
            $bodyText .= "\n\n".$email->getActionText().': '.$email->getActionLink();
        }
        $message->text($bodyText, 'text/plain');

        //construct html body if applicable
        if (EmailType::PLAIN_EMAIL !== $email->getEmailType()) {
            try {
                $message->html($this->renderEmail($email), 'text/html');
            } catch (Exception $e) {
                $this->logger->error('can not render email '.$email->getId());

                return false;
            }
        }

        //send message & check if at least one receiver was reached
        return $this->mailer->send($message) > 0;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function renderEmail(Email $email)
    {
        $arguments = [
            'email' => $email,
            'show_support_info' => false,
            'show_sender_info' => false,
        ];

        if (Email::SENDER_SYSTEM === $email->getSenderName()) {
            $arguments['show_support_info'] = true;
        } elseif (null !== $email->getSenderName() && null !== $email->getSenderEmail()) {
            $arguments['show_sender_info'] = true;
            $arguments['sender_name'] = $email->getSenderName();
            $arguments['sender_email'] = $email->getSenderEmail();
        }

        return $this->twig->render('email/email.html.twig', $arguments);
    }
}
