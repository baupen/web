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
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
class AuthenticationToken extends BaseEntity implements UserInterface
{
    use IdTrait;
    use TimeTrait;

    public const ROLE_API_USER = 'ROLE_API_USER';

    /**
     * @var string
     *
     * @Groups({"authentication-token-read"})
     * @ORM\Column(type="text")
     */
    private $token;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUsedAt;

    /**
     * @var DateTime|null
     *
     * @Groups({"authentication-token-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $accessAllowedBefore;

    /**
     * @var ConstructionManager|null
     *
     * @Groups({"authentication-token-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $constructionManager;

    /**
     * @var Filter|null
     *
     * @Groups({"authentication-token-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Filter")
     */
    private $filter;

    /**
     * @var Craftsman|null
     *
     * @Groups({"authentication-token-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman")
     */
    private $craftsman;

    /**
     * @ORM\PrePersist()
     */
    public function prePersistToken()
    {
        $this->token = HashHelper::getHash();
    }

    /**
     * @Assert\Callback
     */
    public function exactlyOnePayload(ExecutionContextInterface $context)
    {
        $setFields = 0;
        $setFields += $this->getConstructionManager() ? 1 : 0;
        $setFields += $this->getCraftsman() ? 1 : 0;
        $setFields += $this->getFilter() ? 1 : 0;

        if (1 !== $setFields) {
            $context->buildViolation('Exactly one of constructionManager, craftsman or filter must be set.')
                ->atPath('constructionManager')
                ->addViolation();
        }
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

    public function getAccessAllowedBefore(): ?DateTime
    {
        return $this->accessAllowedBefore;
    }

    public function setAccessAllowedBefore(?DateTime $accessAllowedBefore): void
    {
        $this->accessAllowedBefore = $accessAllowedBefore;
    }

    public function getConstructionManager(): ?ConstructionManager
    {
        return $this->constructionManager;
    }

    public function setConstructionManager(ConstructionManager $constructionManager): void
    {
        $this->constructionManager = $constructionManager;
    }

    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

    public function setFilter(Filter $filter): void
    {
        $this->filter = $filter;
    }

    public function getCraftsman(): ?Craftsman
    {
        return $this->craftsman;
    }

    public function setCraftsman(Craftsman $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    public function getRoles()
    {
        return [self::ROLE_API_USER];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->token;
    }

    public function eraseCredentials()
    {
    }
}
