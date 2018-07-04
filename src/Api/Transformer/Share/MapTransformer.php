<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Map;

class MapTransformer extends BatchTransformer
{
    /**
     * @var \App\Api\Transformer\Base\MapTransformer
     */
    private $mapTransformer;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Base\MapTransformer $mapTransformer
     * @param IssueTransformer $issueTransformer
     */
    public function __construct(\App\Api\Transformer\Base\MapTransformer $mapTransformer)
    {
        $this->mapTransformer = $mapTransformer;
    }

    /**
     * @param Map $entity
     *
     * @return \App\Api\Entity\Share\Map
     */
    public function toApi($entity)
    {
        $map = new \App\Api\Entity\Share\Map($entity->getId());
        $this->mapTransformer->writeApiProperties($entity, $map);

        return $map;
    }
}
