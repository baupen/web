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
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * An note is something created by the construction manager to remember something.
 *
 * @ORM\Table(name="issue")
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Note extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var ConstructionManager
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $createdBy;

    /**
     * @var ConstructionSite
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionSite")
     */
    private $constructionSite;

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return ConstructionManager
     */
    public function getCreatedBy(): ConstructionManager
    {
        return $this->createdBy;
    }

    /**
     * @param ConstructionManager $createdBy
     */
    public function setCreatedBy(ConstructionManager $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return ConstructionSite
     */
    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    /**
     * @param ConstructionSite $constructionSite
     */
    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }
}
