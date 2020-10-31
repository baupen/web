<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\DataFixtures;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Service\Interfaces\SampleServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestConstructionSiteFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * @var SampleServiceInterface
     */
    private $sampleService;

    public const ORDER = TestConstructionManagerFixtures::ORDER + 1;
    public const TEST_CONSTRUCTION_SITE_NAME = SampleServiceInterface::TEST;

    /**
     * TestConstructionSiteFixtures constructor.
     */
    public function __construct(SampleServiceInterface $sampleService)
    {
        $this->sampleService = $sampleService;
    }

    public function load(ObjectManager $manager)
    {
        $constructionManagerRepository = $manager->getRepository(ConstructionManager::class);

        /** @var ConstructionManager $constructionManager */
        $constructionManager = $constructionManagerRepository->findOneBy(['email' => TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL]);
        $constructionSite = $this->createAndAssignSampleConstructionSite($constructionManager);
        $manager->persist($constructionSite);
        $manager->persist($constructionManager);

        /** @var ConstructionManager $constructionManager2 */
        $constructionManager2 = $constructionManagerRepository->findOneBy(['email' => TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_2_EMAIL]);
        $constructionSite->getConstructionManagers()->add($constructionManager2);
        $constructionManager2->getConstructionSites()->add($constructionSite);
        $manager->persist($constructionManager2);

        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }

    private function createAndAssignSampleConstructionSite(?ConstructionManager $testUser): ConstructionSite
    {
        $constructionSite = $this->sampleService->createSampleConstructionSite(self::TEST_CONSTRUCTION_SITE_NAME, $testUser);

        $constructionSite->getConstructionManagers()->add($testUser);
        $testUser->getConstructionSites()->add($constructionSite);

        return $constructionSite;
    }
}
