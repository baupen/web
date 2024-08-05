<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use App\Helper\HashHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait AuthenticationTrait
{
    #[Groups(['construction-manager-read-self', 'filter-read'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private ?string $authenticationToken = null;

    public function getAuthenticationToken(): string
    {
        return $this->authenticationToken;
    }

    #[ORM\PrePersist]
    public function setAuthenticationToken(): void
    {
        $this->authenticationToken = HashHelper::getHash();
    }
}
