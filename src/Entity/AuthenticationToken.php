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

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Helper\HashHelper;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An authentication token can be used to authenticate over the API.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "post" = {"security_post_denormalize" = "is_granted('AUTHENTICATION_TOKEN_CREATE', object)", "denormalization_context"={"groups"={"authentication-token-create"}}}
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('AUTHENTICATION_TOKEN_VIEW', object)"}
 *     },
 *     normalizationContext={"groups"={"authentication-token-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"authentication-token-write"}}
 * )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AuthenticationToken extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $token;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $lastUsedAt;

    /**
     * @var DateTime
     *
     * @Groups({"authentication-token-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $accessAllowedBefore;

    /**
     * @var ConstructionManager
     *
     * @Groups({"authentication-token-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $constructionManager;

    /**
     * @var Filter
     *
     * @Groups({"authentication-token-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Filter")
     */
    private $filter;

    /**
     * @var Craftsman
     *
     * @Groups({"authentication-token-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman")
     */
    private $craftsman;

    /**
     * @ORM\PrePersist()
     *
     * @throws Exception
     * @throws Exception
     */
    public function prePersistTime()
    {
        $this->token = HashHelper::getHash();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getLastUsedAt(): DateTime
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(): void
    {
        $this->lastUsedAt = new \DateTime();
    }

    public function getAccessAllowedBefore(): DateTime
    {
        return $this->accessAllowedBefore;
    }

    public function setAccessAllowedBefore(DateTime $accessAllowedBefore): void
    {
        $this->accessAllowedBefore = $accessAllowedBefore;
    }

    public function getConstructionManager(): ConstructionManager
    {
        return $this->constructionManager;
    }

    public function setConstructionManager(ConstructionManager $constructionManager): void
    {
        $this->constructionManager = $constructionManager;
    }

    public function getFilter(): Filter
    {
        return $this->filter;
    }

    public function setFilter(Filter $filter): void
    {
        $this->filter = $filter;
    }

    public function getCraftsman(): Craftsman
    {
        return $this->craftsman;
    }

    public function setCraftsman(Craftsman $craftsman): void
    {
        $this->craftsman = $craftsman;
    }
}
