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
use App\Service\Interfaces\CraftsmanServiceInterface;

class ConstructionSiteService
{
    /**
     * @var CraftsmanServiceInterface
     */
    private $craftsmanService;

    /**
     * ConstructionSiteService constructor.
     */
    public function __construct(CraftsmanServiceInterface $craftsmanService)
    {
        $this->craftsmanService = $craftsmanService;
    }

    private $constructionSiteOverviews = [];

    private function createConstructionSiteOverview(ConstructionSite $constructionSite)
    {
        if (isset($this->constructionSiteOverviews[$constructionSite->getId()])) {
            return $this->constructionSiteOverviews[$constructionSite->getId()];
        }

        $relevantCraftsmen = [];
        foreach ($constructionSite->getCraftsmen() as $craftsman) {
            if (!$craftsman->getIsDeleted()) {
                $relevantCraftsmen[] = $craftsman;
            }
        }

        $lastWeek = new \DateTime('now - 7 days');
        $currentAndPastSummaryLookup = $this->craftsmanService->getCurrentAndPastSummaryLookup($relevantCraftsmen, $lastWeek);
    }
}
