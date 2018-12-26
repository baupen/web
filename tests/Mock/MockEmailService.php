<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
