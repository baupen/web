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

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestFilterFixtures extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = TestConstructionSiteFixtures::ORDER + 1;

    public function load(ObjectManager $manager): void
    {
        $constructionSiteRepository = $manager->getRepository(ConstructionSite::class);
        $constructionSite = $constructionSiteRepository->findOneBy(['name' => TestConstructionSiteFixtures::TEST_CONSTRUCTION_SITE_NAME]);

        $filter = new Filter();
        $filter->setConstructionSite($constructionSite);
        $manager->persist($filter);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return self::ORDER;
    }
}
