<?php

namespace App\Security\Voter;

use App\Entity\EmailTemplate;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

class EmailTemplateVoter extends ConstructionSiteOwnedEntityVoter
{
    public const EMAIL_TEMPLATE_VIEW = 'EMAIL_TEMPLATE_VIEW';
    public const EMAIL_TEMPLATE_MODIFY = 'EMAIL_TEMPLATE_MODIFY';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof EmailTemplate;
    }

    protected function getAllAttributes(): array
    {
        return [self::EMAIL_TEMPLATE_VIEW, self::EMAIL_TEMPLATE_MODIFY];
    }
}
