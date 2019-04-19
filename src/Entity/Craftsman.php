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
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * a craftsman receives information about open issues, and answers them.
 *
 * @ORM\Table(name="craftsman")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Craftsman extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use AddressTrait;

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
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastEmailSent;

    /**
     * @var \DateTime|null
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
    }

    /**
     * @return string
     */
    public function getContactName(): string
    {
        return $this->contactName;
    }

    /**
     * @param string $contactName
     */
    public function setContactName(string $contactName): void
    {
        $this->contactName = $contactName;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getTrade(): string
    {
        return $this->trade;
    }

    /**
     * @param string $trade
     */
    public function setTrade(string $trade): void
    {
        $this->trade = $trade;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
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

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getCompany() . ' (' . $this->getContactName() . ')';
    }

    /**
     * @return \DateTime|null
     */
    public function getLastEmailSent(): ?\DateTime
    {
        return $this->lastEmailSent;
    }

    /**
     * @param \DateTime|null $lastEmailSent
     */
    public function setLastEmailSent(?\DateTime $lastEmailSent): void
    {
        $this->lastEmailSent = $lastEmailSent;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastOnlineVisit(): ?\DateTime
    {
        return $this->lastOnlineVisit;
    }

    /**
     * @param \DateTime|null $lastOnlineVisit
     */
    public function setLastOnlineVisit(?\DateTime $lastOnlineVisit): void
    {
        $this->lastOnlineVisit = $lastOnlineVisit;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastAction()
    {
        $lastAction = $this->getLastOnlineVisit();
        if ($lastAction === null || $lastAction < $this->getLastEmailSent()) {
            return $this->getLastEmailSent();
        }

        return $lastAction;
    }

    /**
     * @return string
     */
    public function getEmailIdentifier(): string
    {
        return $this->emailIdentifier;
    }

    /**
     * @return string
     */
    public function getWriteAuthorizationToken(): string
    {
        return $this->writeAuthorizationToken;
    }

    /**
     * sets the email identifier.
     *
     * @throws \Exception
     */
    public function setEmailIdentifier(): void
    {
        $this->emailIdentifier = Uuid::uuid4()->toString();
        $this->writeAuthorizationToken = Uuid::uuid4()->toString();
    }
}
