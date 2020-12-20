<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataProvider\FeedEntryDataProvider;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Api\Entity\FeedEntry;

class FeedEntryAggregator
{
    /**
     * @var string[][][]
     */
    private $aggregator = [];

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * IssueFeedAggregator constructor.
     */
    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function register(\DateTime $dateTime, object $subject, int $action)
    {
        $iri = $this->iriConverter->getIriFromItem($subject);
        $dateTimeString = $dateTime->format('Y-m-d');

        ++$this->aggregator[$dateTimeString][$iri][$action];
    }

    /**
     * @return FeedEntry[]
     */
    public function createFeedEntries(): array
    {
        krsort($this->aggregator);

        $feedEntries = [];
        foreach ($this->aggregator as $dateTimeString => $iriArray) {
            foreach ($iriArray as $iri => $actions) {
                foreach ($actions as $action => $count) {
                    $feedEntry = new FeedEntry($dateTimeString, $iri, $action, $count);
                    $feedEntries[] = $feedEntry;
                }
            }
        }

        return $feedEntries;
    }
}
