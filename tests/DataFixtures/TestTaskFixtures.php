<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\DataFixtures;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestTaskFixtures extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = TestConstructionSiteFixtures::ORDER + TestConstructionManagerFixtures::ORDER + 1;
    public const TEST_TEXT_ENTRY = 'hello world';

    public function load(ObjectManager $manager): void
    {
        $constructionSiteRepository = $manager->getRepository(ConstructionSite::class);
        $constructionSite = $constructionSiteRepository->findOneBy(['name' => TestConstructionSiteFixtures::TEST_CONSTRUCTION_SITE_NAME]);

        $constructionManagerRepository = $manager->getRepository(ConstructionManager::class);
        $constructionManager = $constructionManagerRepository->findOneBy(['email' => TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL]);

        $issueEvent = new Task();
        $issueEvent->setConstructionSite($constructionSite);
        $issueEvent->setDescription(self::TEST_TEXT_ENTRY);
        $issueEvent->setCreatedAt(new \DateTime());
        $issueEvent->setCreatedBy($constructionManager);
        $manager->persist($issueEvent);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return self::ORDER;
    }
}
