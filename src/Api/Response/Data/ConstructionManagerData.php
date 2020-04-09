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

use App\Api\Entity\Base\ConstructionManager;

class ConstructionManagerData
{
    /**
     * @var ConstructionManager
     */
    private $constructionManager;

    public function getConstructionManager(): ConstructionManager
    {
        return $this->constructionManager;
    }

    public function setConstructionManager(ConstructionManager $constructionManager): void
    {
        $this->constructionManager = $constructionManager;
    }
}
