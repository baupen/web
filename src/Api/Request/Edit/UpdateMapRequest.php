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

use App\Api\Entity\Edit\UpdateMap;
use App\Api\Request\ConstructionSiteRequest;

class UpdateMapRequest extends ConstructionSiteRequest
{
    /**
     * @var UpdateMap
     */
    private $map;

    public function getMap(): UpdateMap
    {
        return $this->map;
    }

    public function setMap(UpdateMap $map): void
    {
        $this->map = $map;
    }
}
