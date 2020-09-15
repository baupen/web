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

use App\Entity\ConstructionManager;
use App\Entity\Email;

interface EmailServiceInterface
{
    /**
     * @return bool
     */
    public function sendRegisterConfirm(ConstructionManager $constructionManager);

    /**
     * @return string
     */
    public function renderEmail(Email $email);
}
