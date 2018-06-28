<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/28/18
 * Time: 2:01 PM
 */

namespace App\Tests\Mock;


use App\Entity\Email;
use App\Service\Interfaces\EmailServiceInterface;

class MockEmailService implements EmailServiceInterface
{
    /**
     * @var string[]
     */
    private $receivers = [];

    /**
     * @param Email $email
     * @param string[] $options
     *
     * @return bool
     */
    public function sendEmail(Email $email, $options = [])
    {
        $this->receivers[] = $email->getReceiver();
        return true;
    }

    /**
     * @return string[]
     */
    public function getReceivers(): array
    {
        return $this->receivers;
    }
}