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

    /**
     * @return string|null
     */
    public function getProposedGivenName(): ?string
    {
        return $this->proposedGivenName;
    }

    /**
     * @param string|null $proposedGivenName
     */
    public function setProposedGivenName(?string $proposedGivenName): void
    {
        $this->proposedGivenName = $proposedGivenName;
    }

    /**
     * @return string|null
     */
    public function getProposedFamilyName(): ?string
    {
        return $this->proposedFamilyName;
    }

    /**
     * @param string|null $proposedFamilyName
     */
    public function setProposedFamilyName(?string $proposedFamilyName): void
    {
        $this->proposedFamilyName = $proposedFamilyName;
    }
}
