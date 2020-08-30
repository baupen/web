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

use App\Api\Entity\Base\ConstructionSite;

class ConstructionSitesData
{
    /**
     * @var ConstructionSite[]
     */
    private $constructionSites;

    /**
     * @return ConstructionSite[]
     */
    public function getConstructionSites(): array
    {
        return $this->constructionSites;
    }

    /**
     * @param ConstructionSite[] $constructionSites
     */
    public function setConstructionSites(array $constructionSites): void
    {
        $this->constructionSites = $constructionSites;
    }
}
