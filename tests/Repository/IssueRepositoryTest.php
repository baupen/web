<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 7/21/18
 * Time: 6:34 PM
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
        $this->assertSameSize($repo->findBy(["map" => $constructionSite->getMapIds()]), $repo->filter($filter));
    }
}