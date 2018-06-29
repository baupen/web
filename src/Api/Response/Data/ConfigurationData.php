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

use App\Api\Entity\Base\BaseEntity;

class ConfigurationData
{
    /**
     * @var BaseEntity
     */
    private $constructionSite;

    /**
     * @return BaseEntity
     */
    public function getConstructionSite(): BaseEntity
    {
        return $this->constructionSite;
    }

    /**
     * @param BaseEntity $constructionSite
     */
    public function setConstructionSite(BaseEntity $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }
}
