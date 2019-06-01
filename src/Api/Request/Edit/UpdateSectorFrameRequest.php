<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request\Edit;

use App\Api\Request\ConstructionSiteRequest;
use App\Model\Frame;

class UpdateSectorFrameRequest extends ConstructionSiteRequest
{
    /**
     * @var Frame
     */
    private $sectorFrame;

    /**
     * @return Frame
     */
    public function getSectorFrame(): Frame
    {
        return $this->sectorFrame;
    }

    /**
     * @param Frame $sectorFrame
     */
    public function setSectorFrame(Frame $sectorFrame): void
    {
        $this->sectorFrame = $sectorFrame;
    }
}
