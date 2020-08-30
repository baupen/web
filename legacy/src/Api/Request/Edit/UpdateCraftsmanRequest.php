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

use App\Api\Entity\Edit\UpdateCraftsman;
use App\Api\Request\ConstructionSiteRequest;

class UpdateCraftsmanRequest extends ConstructionSiteRequest
{
    /**
     * @var UpdateCraftsman
     */
    private $craftsman;

    public function getCraftsman(): UpdateCraftsman
    {
        return $this->craftsman;
    }

    public function setCraftsman(UpdateCraftsman $craftsman): void
    {
        $this->craftsman = $craftsman;
    }
}
