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
 * A IssueEventFile is a file attached together with the protocol entry.
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class IssueEventFile extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    #[ORM\ManyToOne(targetEntity: IssueEvent::class)]
    private IssueEvent $createdFor;

    public function getCreatedFor(): IssueEvent
    {
        return $this->createdFor;
    }

    public function setCreatedFor(IssueEvent $createdFor): void
    {
        $this->createdFor = $createdFor;
    }
}
