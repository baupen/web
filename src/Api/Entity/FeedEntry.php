<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filters\UnmappedConstructionSiteFilter;
use App\Entity\Craftsman;
use DateTime;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={},
 *     normalizationContext={"groups"={"feed-entry-read"}, "skip_null_values"=false}
 * )
 * @ApiFilter(UnmappedConstructionSiteFilter::class)
 */
class FeedEntry
{
    /**
     * @var DateTime
     *
     * @Groups({"feed-entry-read"})
     */
    private $timestamp;

    /**
     * @var Craftsman
     *
     * @Groups({"feed-entry-read"})
     */
    private $craftsman;

    /**
     * @var int
     *
     * @Groups({"feed-entry-read"})
     */
    private $count;
}
