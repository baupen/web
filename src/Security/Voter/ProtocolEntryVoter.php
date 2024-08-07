<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\ProtocolEntry;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

class ProtocolEntryVoter extends ConstructionSiteOwnedEntityVoter
{
    public const PROTOCOL_ENTRY_VIEW = 'PROTOCOL_ENTRY_VIEW';
    public const PROTOCOL_ENTRY_MODIFY = 'PROTOCOL_ENTRY_MODIFY';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof ProtocolEntry;
    }

    protected function getAllAttributes(): array
    {
        return [self::PROTOCOL_ENTRY_VIEW, self::PROTOCOL_ENTRY_MODIFY];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::PROTOCOL_ENTRY_VIEW];
    }
}
