<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Repository;

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Tests\Controller\Base\FixturesTestCase;

class IssueRepositoryTest extends FixturesTestCase
{
    public function testFilter()
    {
        $doctrine = $this->getDoctrine();
        $constructionSite = $doctrine->getRepository(ConstructionSite::class)->findOneBy([]);

        $repo = $doctrine->getRepository(Issue::class);

        $filter = new Filter();
        $filter->setConstructionSite($constructionSite->getId());
        $this->assertSameSize($repo->findBy(['map' => $constructionSite->getMapIds()]), $repo->findByFilter($filter));
    }
}
