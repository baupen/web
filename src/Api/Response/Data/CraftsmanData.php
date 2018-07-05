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

use App\Api\Entity\Base\Craftsman;

class CraftsmanData
{
    /**
     * @var Craftsman[]
     */
    private $craftsman;

    /**
     * @return Craftsman[]
     */
    public function getCraftsman(): Craftsman
    {
        return $this->craftsman;
    }

    /**
     * @param Craftsman[] $craftsman
     */
    public function setCraftsman(Craftsman $craftsman): void
    {
        $this->craftsman = $craftsman;
    }
}
