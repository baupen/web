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
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class EmailService implements EmailServiceInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var string
     */
    private $contactEmail;

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
     *
     * @param \Swift_Mailer $mailer
     * @param LoggerInterface $logger
     * @param Environment $twig
     * @param string $contactEmail
     */
    public function __construct(\Swift_Mailer $mailer, LoggerInterface $logger, Environment $twig, string $contactEmail)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->contactEmail = $contactEmail;
    }

    /**
     * @param Email $email
     *
     * @return bool
     */
    public function sendEmail(Email $email)
    {
        $message = (new \Swift_Message())
            ->setSubject($email->getSubject())
            ->setFrom($this->contactEmail)
            ->setTo($email->getReceiver());

        $bodyText = $email->getBody();
        if (null !== $email->getActionLink()) {
            $bodyText .= "\n\n" . $email->getActionText() . ': ' . $email->getActionLink();
        }
        $message->setBody($bodyText, 'text/plain');

        if (EmailType::PLAIN_EMAIL !== $email->getEmailType()) {
            try {
                $message->addPart(
                    $this->twig->render(
                        'email/email.html.twig',
                        ['email' => $email]
                    ),
                    'text/html'
                );
            } catch (\Exception $e) {
                $this->logger->error('can not render email ' . $email->getId());

                return false;
            }
        }

        return $this->mailer->send($message) > 0;
    }
}
