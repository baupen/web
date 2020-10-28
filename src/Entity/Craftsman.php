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
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * a craftsman receives information about open issues, and answers them.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Craftsman extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use SoftDeleteTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $contactName;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $trade;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @var ConstructionSite
     *
     * @ORM\ManyToOne(targetEntity="ConstructionSite", inversedBy="craftsmen")
     */
    private $constructionSite;

    /**
     * @var Issue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="craftsman")
     */
    private $issues;

    /**
     * @var Issue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="responseBy")
     */
    private $respondedIssues;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastEmailSent;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastOnlineVisit;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $emailIdentifier;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $writeAuthorizationToken;

    /**
     * Craftsman constructor.
     */
    public function __construct()
    {
        $this->issues = new ArrayCollection();
        $this->respondedIssues = new ArrayCollection();
    }

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): void
    {
        $this->contactName = $contactName;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getTrade(): string
    {
        return $this->trade;
    }

    public function setTrade(string $trade): void
    {
        $this->trade = $trade;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getRespondedIssues()
    {
        return $this->respondedIssues;
    }

    public function getName(): string
    {
        return $this->getCompany().' ('.$this->getContactName().')';
    }

    public function getLastEmailSent(): ?DateTime
    {
        return $this->lastEmailSent;
    }

    public function setLastEmailSent(?DateTime $lastEmailSent): void
    {
        $this->lastEmailSent = $lastEmailSent;
    }

    public function getLastOnlineVisit(): ?DateTime
    {
        return $this->lastOnlineVisit;
    }

    public function setLastOnlineVisit(?DateTime $lastOnlineVisit): void
    {
        $this->lastOnlineVisit = $lastOnlineVisit;
    }

    /**
     * @return DateTime|null
     */
    public function getLastAction()
    {
        $lastAction = $this->getLastOnlineVisit();
        if (null === $lastAction || $lastAction < $this->getLastEmailSent()) {
            return $this->getLastEmailSent();
        }

        return $lastAction;
    }

    public function getEmailIdentifier(): string
    {
        return $this->emailIdentifier;
    }

    public function getWriteAuthorizationToken(): string
    {
        return $this->writeAuthorizationToken;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersistCraftsman(): void
    {
        $this->emailIdentifier = Uuid::v4();
        $this->writeAuthorizationToken = Uuid::v4();
    }

    public function canRemove()
    {
        return 0 === $this->issues->count() && 0 === $this->respondedIssues->count();
    }
}
