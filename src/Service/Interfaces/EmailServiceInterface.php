<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\Email;

interface EmailServiceInterface
{
    /**
     * @param Email    $email
     * @param string[] $options
     *
     * @return bool
     */
    public function sendEmail(Email $email, $options = []);
}
