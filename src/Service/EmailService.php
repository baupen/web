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
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;

class EmailService implements EmailServiceInterface
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $mailerSender;

    /**
     * @var string
     */
    private $supportEmail;

    /**
     * @var string
     */
    private $sslValidation;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Swift_Transport
     */
    private $transport;

    /**
     * EmailService constructor.
     */
    public function __construct(Swift_Mailer $mailer, \Swift_Transport $transport, LoggerInterface $logger, Environment $twig, string $mailerSender, string $supportEmail, string $sslValidation)
    {
        $this->mailer = $mailer;
        $this->transport = $transport;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->mailerSender = $mailerSender;
        $this->supportEmail = $supportEmail;
        $this->sslValidation = $sslValidation;
    }

    /**
     * @param string[] $options
     *
     * @return bool
     */
    public function sendEmail(Email $email, $options = [])
    {
        $message = (new Swift_Message())
            ->setSubject($email->getSubject())
            ->setFrom($this->mailerSender)
            ->setTo($email->getReceiver())
        ;

        //set reply to
        if (isset($options['reply_to'])) {
            $message->setReplyTo($options['reply_to']);
        } else {
            $message->setReplyTo($this->supportEmail);
        }

        //construct plain body
        $bodyText = $email->getBody();
        if ($email->getActionLink() !== null) {
            $bodyText .= "\n\n" . $email->getActionText() . ': ' . $email->getActionLink();
        }
        $message->setBody($bodyText, 'text/plain');

        //construct html body if applicable
        if ($email->getEmailType() !== EmailType::PLAIN_EMAIL) {
            try {
                $arguments = ['email' => $email,
                    'show_support_info' => false,
                    'show_sender_info' => false,
                ];

                if ($email->getSenderName() === Email::SENDER_SYSTEM) {
                    $arguments['show_support_info'] = true;
                } elseif ($email->getSenderName() !== null && $email->getSenderEmail() !== null) {
                    $arguments['show_sender_info'] = true;
                    $arguments['sender_name'] = $email->getSenderName();
                    $arguments['sender_email'] = $email->getSenderEmail();
                }

                $message->addPart(
                    $this->twig->render('email/email.html.twig', $arguments), 'text/html'
                );
            } catch (Exception $e) {
                $this->logger->error('can not render email ' . $email->getId());

                return false;
            }
        }

        if ($this->sslValidation === 'disabled' && $this->transport instanceof \Swift_Transport_EsmtpTransport) {
            $this->transport->setStreamOptions([
                'ssl' => ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false],
            ]);
        }

        //send message & check if at least one receiver was reached
        return $this->mailer->send($message) > 0;
    }
}
