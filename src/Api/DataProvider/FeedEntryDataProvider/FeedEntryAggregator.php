<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataProvider\FeedEntryDataProvider;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Api\Entity\FeedEntry;
use App\Helper\DateTimeFormatter;

class FeedEntryAggregator
{
    /**
     * @var string[][][]
     */
    private array $aggregator = [];

    private IriConverterInterface $iriConverter;

    /**
     * IssueFeedAggregator constructor.
     */
    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function register(\DateTime $dateTime, object $subject, int $action): void
    {
        $iri = $this->iriConverter->getIriFromItem($subject);
        $dateTimeString = $dateTime->format(DateTimeFormatter::ISO_DATE_FORMAT);

        if (!isset($this->aggregator[$dateTimeString][$iri][$action])) {
            $this->aggregator[$dateTimeString][$iri][$action] = 0;
        }

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
