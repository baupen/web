<?php

/*
 * This file is part of the mangel.io project.
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
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class IssueImage extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    /**
     * @var Issue
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Issue", inversedBy="images")
     */
    private $issue;

    /**
     * @return Issue
     */
    public function getIssue(): Issue
    {
        return $this->issue;
    }

    /**
     * @param Issue $issue
     */
    public function setIssue(Issue $issue): void
    {
        $this->issue = $issue;
    }
}
