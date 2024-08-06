<?php

/*
 * This file is part of the baupen project.
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
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use Doctrine\Persistence\ManagerRegistry;

trait TestDataTrait
{
    private function getIriFromItem($item)
    {
        return static::getClient()->getContainer()->get('api_platform.iri_converter')->getIriFromItem($item);
    }

    private function getTestConstructionManager(): ConstructionManager
    {
        return $this->getConstructionManagerByEmail(TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL);
    }

    private function getTestAssociatedConstructionManager(): ConstructionManager
    {
        return $this->getConstructionManagerByEmail(TestConstructionManagerFixtures::ASSOCIATED_CONSTRUCTION_MANAGER_EMAIL);
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
        $registry = static::getClient()->getContainer()->get(ManagerRegistry::class);
        $constructionSiteRepository = $registry->getRepository(ConstructionSite::class);

        return $constructionSiteRepository->findOneBy(['name' => $constructionSiteName]);
    }

    private function getConstructionManagerByEmail(string $constructionManagerEmail): ConstructionManager
    {
        /** @var ManagerRegistry $registry */
        $registry = static::getClient()->getContainer()->get(ManagerRegistry::class);
        $constructionManagerRepository = $registry->getRepository(ConstructionManager::class);

        return $constructionManagerRepository->findOneBy(['email' => $constructionManagerEmail]);
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

    private function assignConstructionManager(ConstructionSite $constructionSite, ConstructionManager $manager): void
    {
        $constructionSite->getConstructionManagers()->add($manager);
        $this->saveEntity($constructionSite);
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
