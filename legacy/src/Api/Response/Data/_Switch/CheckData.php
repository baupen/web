<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data\_Switch;

class CheckData
{
    /**
     * @var bool
     */
    private $constructionSiteNameTaken;

    public function isConstructionSiteNameTaken(): bool
    {
        return $this->constructionSiteNameTaken;
    }

    public function setConstructionSiteNameTaken(bool $constructionSiteNameTaken): void
    {
        $this->constructionSiteNameTaken = $constructionSiteNameTaken;
    }
}
