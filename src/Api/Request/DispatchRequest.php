<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class DispatchRequest extends ConstructionSiteRequest
{
    /**
     * @var string[]
     *
     * @Assert\NotNull()
     */
    private $craftsmanIds;

    /**
     * @return string[]
     */
    public function getCraftsmanIds(): array
    {
        return $this->craftsmanIds;
    }

    /**
     * @param string[] $craftsmanIds
     */
    public function setCraftsmanIds(array $craftsmanIds): void
    {
        $this->craftsmanIds = $craftsmanIds;
    }
}
