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

use App\Api\Entity\Edit\UpdateConstructionSite;
use App\Api\Request\ConstructionSiteRequest;

class UpdateConstructionSiteRequest extends ConstructionSiteRequest
{
    /**
     * @var UpdateConstructionSite
     */
    private $constructionSite;

    public function getConstructionSite(): UpdateConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(UpdateConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }
}
