<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use Doctrine\Persistence\ManagerRegistry;

trait TestDataTrait
{
    private function getIriFromItem($item)
    {
        return static::$container->get('api_platform.iri_converter')->getIriFromItem($item);
    }

    private function getTestConstructionSite(): ConstructionSite
    {
        return $this->getConstructionSiteByName(TestConstructionSiteFixtures::TEST_CONSTRUCTION_SITE_NAME);
    }

    private function getEmptyConstructionSite(): ConstructionSite
    {
        return $this->getConstructionSiteByName(TestConstructionSiteFixtures::EMPTY_CONSTRUCTION_SITE_NAME);
    }

    private function getConstructionSiteByName(string $constructionSiteName): ConstructionSite
    {
        /** @var ManagerRegistry $registry */
        $registry = static::$container->get(ManagerRegistry::class);
        $constructionSiteRepository = $registry->getRepository(ConstructionSite::class);

        return $constructionSiteRepository->findOneBy(['name' => $constructionSiteName]);
    }

    private function addConstructionManager(ConstructionSite $constructionSite, string $email = 'some@mail.com'): ConstructionManager
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->getConstructionSites()->add($constructionSite);
        $constructionManager->setEmail($email);

        $this->saveEntity($constructionManager);

        return $constructionManager;
    }

    private function addCraftsman(ConstructionSite $constructionSite, string $name = 'craft'): Craftsman
    {
        $craftsman = new Craftsman();
        $craftsman->setConstructionSite($constructionSite);
        $craftsman->setContactName($name);
        $craftsman->setEmail($name.'@ch.ch');
        $craftsman->setCompany($name.' AG');
        $craftsman->setTrade($name);

        $this->saveEntity($craftsman);

        return $craftsman;
    }

    private function addFilter(ConstructionSite $constructionSite): Filter
    {
        $filter = new Filter();
        $filter->setConstructionSite($constructionSite);

        $this->saveEntity($filter);

        return $filter;
    }

    private function addMap(ConstructionSite $constructionSite, string $name = 'empty'): Map
    {
        $map = new Map();
        $map->setConstructionSite($constructionSite);
        $map->setName($name);

        $this->saveEntity($map);

        return $map;
    }

    private function addMapFile(ConstructionSite $constructionSite): MapFile
    {
        $mapFile = new MapFile();
        $mapFile->setConstructionSite($constructionSite);
        $mapFile->setFilename('invalid.pdf');
        $mapFile->setHash('some invalid hash');

        $this->saveEntity($mapFile);

        return $mapFile;
    }

    private function addIssue(ConstructionSite $constructionSite, ConstructionManager $manager): Issue
    {
        $issue = new Issue();
        $issue->setConstructionSite($constructionSite);
        $issue->setNumber(999);
        $issue->setCreatedAt(new \DateTime());
        $issue->setCreatedBy($manager);

        $this->saveEntity($issue);

        return $issue;
    }
}
