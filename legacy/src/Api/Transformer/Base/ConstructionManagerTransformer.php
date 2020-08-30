<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Base;

use App\Entity\ConstructionManager;

class ConstructionManagerTransformer
{
    /**
     * @param ConstructionManager                      $source
     * @param \App\Api\Entity\Base\ConstructionManager $target
     */
    public function writeApiProperties($source, $target)
    {
        $target->setEmail($source->getEmail());
    }

    /**
     * @param ConstructionManager $constructionManager
     *
     * @return \App\Api\Entity\Base\ConstructionManager
     */
    public function toApi($constructionManager)
    {
        $api = new \App\Api\Entity\Base\ConstructionManager($constructionManager->getId());
        $this->writeApiProperties($constructionManager, $api);

        return $api;
    }
}
