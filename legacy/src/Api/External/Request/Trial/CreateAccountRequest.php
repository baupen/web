<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Request\Trial;

use App\Api\Request\Base\AbstractRequest;

class CreateAccountRequest extends AbstractRequest
{
    /**
     * @var string|null
     */
    private $proposedGivenName;

    /**
     * @var string|null
     */
    private $proposedFamilyName;

    public function getProposedGivenName(): ?string
    {
        return $this->proposedGivenName;
    }

    public function setProposedGivenName(?string $proposedGivenName): void
    {
        $this->proposedGivenName = $proposedGivenName;
    }

    public function getProposedFamilyName(): ?string
    {
        return $this->proposedFamilyName;
    }

    public function setProposedFamilyName(?string $proposedFamilyName): void
    {
        $this->proposedFamilyName = $proposedFamilyName;
    }
}
