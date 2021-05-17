<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\ConstructionManager;

interface UserServiceInterface
{
    public const REGISTRATION_FAIL_ALREADY_REGISTERED = 'REGISTRATION_FAIL_ALREADY_REGISTERED';
    public const REGISTRATION_FAIL_ACCOUNT_DISABLED = 'REGISTRATION_FAIL_ACCOUNT_DISABLED';
    public const REGISTRATION_FAIL_EMAIL_NOT_SENT = 'REGISTRATION_FAIL_EMAIL_NOT_SENT';

    public function authorize(ConstructionManager $constructionManager): void;

    public function refreshAuthorization(ConstructionManager $constructionManager): void;

    public function setDefaultValues(ConstructionManager $constructionManager): void;

    public function tryRegister(ConstructionManager $template, ?string &$error = null): bool;
}
