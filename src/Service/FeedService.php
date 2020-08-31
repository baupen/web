<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Service\Feed\Entity\FeedEntry;
use App\Service\Feed\ResponseReceivedGeneratorIssue;
use App\Service\Feed\VisitedWebpageGeneratorIssue;
use Doctrine\Persistence\ManagerRegistry;

class FeedService
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * FeedService constructor.
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return FeedEntry[]
     */
    public function getFeedEntries(ConstructionSite $constructionSite): array
    {
        $issues = $this->doctrine->getRepository(Issue::class)->findByRecentlyChanged($constructionSite);
        $craftsmen = $constructionSite->getCraftsmen();

        $feedEntries = [];
        $counter = 0;
        $responseReceived = new ResponseReceivedGeneratorIssue();
        foreach ($responseReceived->getFeedEntries($issues) as $entry) {
            $feedEntries[$entry->getTimestamp()->format('c').$counter++] = $entry;
        }

        $craftsmanFeedEntryGenerator = new VisitedWebpageGeneratorIssue();
        foreach ($craftsmanFeedEntryGenerator->getFeedEntries($craftsmen) as $entry) {
            $feedEntries[$entry->getTimestamp()->format('c').$counter++] = $entry;
        }

        krsort($feedEntries);

        return array_values($feedEntries);
    }
}
