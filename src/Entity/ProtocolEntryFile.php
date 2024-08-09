<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\FileTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * A ProtocolEntryFile is a file attached together with the protocol entry.
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class ProtocolEntryFile extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    #[ORM\ManyToOne(targetEntity: ProtocolEntry::class)]
    private ProtocolEntry $createdFor;

    public function getCreatedFor(): ProtocolEntry
    {
        return $this->createdFor;
    }

    public function setCreatedFor(ProtocolEntry $createdFor): void
    {
        $this->createdFor = $createdFor;
    }
}
