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

use App\Api\Entity\Base\PublicMap;
use App\Entity\Map;

class PublicMapTransformer
{
    /**
     * @var MapTransformer
     */
    private $mapTransformer;

    /**
     * PublicIssueTransformer constructor.
     */
    public function __construct(MapTransformer $mapTransformer)
    {
        $this->mapTransformer = $mapTransformer;
    }

    /**
     * @param Map       $source
     * @param PublicMap $target
     */
    public function writeApiProperties($source, $target)
    {
        $this->mapTransformer->writeApiProperties($source, $target);
        $target->setContext($source->getContext());
    }
}
