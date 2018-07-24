<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Register;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Craftsman;

class CraftsmanTransformer extends BatchTransformer
{
    /**
     * @var \App\Api\Transformer\Foyer\CraftsmanTransformer
     */
    private $craftsmanTransformer;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Foyer\CraftsmanTransformer $craftsmanTransformer
     */
    public function __construct(\App\Api\Transformer\Foyer\CraftsmanTransformer $craftsmanTransformer)
    {
        $this->craftsmanTransformer = $craftsmanTransformer;
    }

    /**
     * @param Craftsman $entity
     *
     * @return \App\Api\Entity\Register\Craftsman
     */
    public function toApi($entity)
    {
        $craftsman = new \App\Api\Entity\Register\Craftsman($entity->getId());
        $this->craftsmanTransformer->writeApiProperties($entity, $craftsman);

        return $craftsman;
    }
}
