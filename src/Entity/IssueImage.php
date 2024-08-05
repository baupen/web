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
 * A issue image is the image taken in connection with the issue.
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class IssueImage extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    #[ORM\ManyToOne(targetEntity: Issue::class)]
    private Issue $createdFor;

    public function getCreatedFor(): Issue
    {
        return $this->createdFor;
    }

    public function setCreatedFor(Issue $createdFor): void
    {
        $this->createdFor = $createdFor;
    }
}
