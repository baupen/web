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

class ConstructionManagersData
{
    /**
     * @var ConstructionManager[]
     */
    private $constructionManagers;

    /**
     * @return ConstructionManager[]
     */
    public function getConstructionManagers(): array
    {
        return $this->constructionManagers;
    }

    /**
     * @param ConstructionManager[] $constructionManagers
     */
    public function setConstructionManagers(array $constructionManagers): void
    {
        $this->constructionManagers = $constructionManagers;
    }
}
