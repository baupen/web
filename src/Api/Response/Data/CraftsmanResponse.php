<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data;

use App\Api\External\Entity\Craftsman;

class CraftsmanResponse
{
    /**
     * @var Craftsman[]
     */
    private $craftsmen;

    /**
     * @return Craftsman[]
     */
    public function getCraftsmen(): array
    {
        return $this->craftsmen;
    }

    /**
     * @param Craftsman[] $craftsmen
     */
    public function setCraftsmen(array $craftsmen): void
    {
        $this->craftsmen = $craftsmen;
    }
}
