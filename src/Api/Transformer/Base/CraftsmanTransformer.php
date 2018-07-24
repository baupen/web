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

use App\Entity\Craftsman;

class CraftsmanTransformer
{
    /**
     * @param Craftsman $source
     * @param \App\Api\Entity\Base\Craftsman $target
     */
    public function writeApiProperties($source, $target)
    {
        $target->setName($source->getName());
        $target->setTrade($source->getTrade());
    }

    /**
     * @param Craftsman $craftsman
     *
     * @return \App\Api\Entity\Base\Craftsman
     */
    public function toApi($craftsman)
    {
        $api = new \App\Api\Entity\Base\Craftsman($craftsman->getId());
        $this->writeApiProperties($craftsman, $api);

        return $api;
    }
}
