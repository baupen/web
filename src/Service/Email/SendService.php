<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Email;

use App\Entity\Email;
use App\Enum\EmailType;
use Symfony\Component\Mailer\MailerInterface;

class SendService
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
     * EmailService constructor.
     */
    public function __construct(MailerInterface $mailer, string $mailerFromEmail)
    {
        $this->mailer = $mailer;
        $this->mailerFromEmail = $mailerFromEmail;
    }

    /**
     * @param string[] $options
     *
     * @return bool
     */
    public function sendEmail(Email $email, string $html)
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
            $message->html($html, 'text/html');
        }

        //send message & check if at least one receiver was reached
        return $this->mailer->send($message) > 0;
    }
}
