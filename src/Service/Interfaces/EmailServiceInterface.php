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

interface EmailServiceInterface
{
    public function sendRegisterConfirmLink(ConstructionManager $constructionManager): bool;

    public function sendAppInvitation(ConstructionManager $constructionManager): bool;

    public function sendRecoverConfirmLink(ConstructionManager $constructionManager): bool;
}
