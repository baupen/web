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
use App\Entity\Filter;
use App\Service\Interfaces\FilterServiceInterface;
use Doctrine\Persistence\ManagerRegistry;

class FilterService implements FilterServiceInterface
{
    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * FilterService constructor.
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    public function createFromQuery(array $filters): Filter
    {
        $constructionSiteId = $filters['constructionSite'];
        $constructionSiteRepo = $this->manager->getRepository(ConstructionSite::class);
        $constructionSite = $constructionSiteRepo->find($constructionSiteId);

        if (null === $constructionSite) {
            throw new \InvalidArgumentException('The filter must have a valid construction site set.');
        }

        $filter = new Filter();
        $filter->setConstructionSite($constructionSite);

        // TODO: add the other properties of the filter

        return $filter;
    }
}
