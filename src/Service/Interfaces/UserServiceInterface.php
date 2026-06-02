<?php

namespace App\Service\Interfaces;

use App\Entity\ConstructionManager;

interface UserServiceInterface
{
    public const string REGISTRATION_FAIL_ALREADY_REGISTERED = 'REGISTRATION_FAIL_ALREADY_REGISTERED';
    public const string REGISTRATION_FAIL_ACCOUNT_DISABLED = 'REGISTRATION_FAIL_ACCOUNT_DISABLED';
    public const string REGISTRATION_FAIL_EMAIL_NOT_SENT = 'REGISTRATION_FAIL_EMAIL_NOT_SENT';
    public const string AUTHORIZATION_AUTHORITY_WHITELIST = 'AUTHORIZATION_AUTHORITY_WHITELIST';

    public function authorize(ConstructionManager $constructionManager): void;

    public function refreshAuthorization(ConstructionManager $constructionManager): void;

    public function setDefaultValues(ConstructionManager $constructionManager): void;

    public function tryRegister(ConstructionManager $template, ?string &$error = null): bool;
}
