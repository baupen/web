<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Feed;

use App\Api\Transformer\Base\CraftsmanTransformer;
use App\Feed\Entity\FeedEntry;

class FeedTransformer
{
    private static $id = 1;

    /**
     * @var CraftsmanTransformer
     */
    private $craftsmanTransformer;

    public function __construct(CraftsmanTransformer $craftsmanTransformer)
    {
        $this->craftsmanTransformer = $craftsmanTransformer;
    }

    /**
     * @param FeedEntry $entity
     * @param string $type
     *
     * @return \App\Api\Entity\Feed\FeedEntry
     */
    public function toApi($entity, string $type)
    {
        $feedEntry = new \App\Api\Entity\Feed\FeedEntry(self::$id++);
        $feedEntry->setCraftsman($this->craftsmanTransformer->toApi($entity->getCraftsman()));
        $feedEntry->setType($type);
        $feedEntry->setCount($entity->getCount());
        $feedEntry->setTimestamp($entity->getTimestamp());

        return $feedEntry;
    }
}
