<?php

namespace App\Security\Voter\Internal;

use App\Entity\ConstructionSite;
use App\Entity\EmailTemplate;
use App\Entity\Task;

/**
 * @extends AbstractConstructionSiteInternalVoter<EmailTemplate>
 */
class EmailTemplateVoter extends AbstractConstructionSiteInternalVoter
{
    public const string EMAIL_TEMPLATE_VIEW = 'EMAIL_TEMPLATE_VIEW';
    public const string EMAIL_TEMPLATE_MODIFY = 'EMAIL_TEMPLATE_MODIFY';

    protected function isInstanceOf(mixed $subject): bool
    {
        return $subject instanceof EmailTemplate;
    }

    protected function getConstructionSite(mixed $subject): ?ConstructionSite
    {
        return $subject->getConstructionSite();
    }

    protected function getRoles(): array
    {
        return [self::EMAIL_TEMPLATE_VIEW, self::EMAIL_TEMPLATE_MODIFY];
    }
}
