<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\FileTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * A IssueEventFile is a file attached together with the issue event.
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
