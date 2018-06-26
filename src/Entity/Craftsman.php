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
        return $this->getContactName() . ' (' . $this->getCompany() . ')';
    }
}
