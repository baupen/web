<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data\Edit;

use App\Model\Frame;

class SectorFrameData
{
    /**
     * @var Frame|null
     */
    private $sectorFrame;

    /**
     * @return Frame|null
     */
    public function getSectorFrame(): ?Frame
    {
        return $this->sectorFrame;
    }

    /**
     * @param Frame|null $sectorFrame
     */
    public function setSectorFrame(?Frame $sectorFrame): void
    {
        $this->sectorFrame = $sectorFrame;
    }
}
