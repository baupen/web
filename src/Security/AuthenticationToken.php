<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use App\Entity\Base\BaseEntity;
use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The authentication token authenticates API calls.
 */
class AuthenticationToken extends BaseEntity implements UserInterface
{
    use IdTrait;
    use TimeTrait;

    public const ROLE_API_USER = 'ROLE_API_USER';

    private string $token;

    private ?ConstructionManager $constructionManager = null;

    private ?Filter $filter = null;

    private ?Craftsman $craftsman = null;

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
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

    public function getRoles(): array
    {
        return [self::ROLE_API_USER];
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->token;
    }

    public function getUserIdentifier(): string
    {
        return $this->token;
    }

    public function eraseCredentials()
    {
    }
}
