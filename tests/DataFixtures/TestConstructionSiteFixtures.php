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
    public const EMPTY_CONSTRUCTION_SITE_NAME = 'empty';

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
        $constructionSite = $this->sampleService->createSampleConstructionSite(self::TEST_CONSTRUCTION_SITE_NAME, $constructionManager);
        $manager->persist($constructionSite);
        $manager->persist($constructionManager);

        /** @var ConstructionManager $associatedConstructionManager */
        $associatedConstructionManager = $constructionManagerRepository->findOneBy(['email' => TestConstructionManagerFixtures::ASSOCIATED_CONSTRUCTION_MANAGER_EMAIL]);
        $constructionSite->getConstructionManagers()->add($associatedConstructionManager);
        $associatedConstructionManager->getConstructionSites()->add($constructionSite);
        $manager->persist($associatedConstructionManager);

        $constructionSite = $this->createEmptyConstructionSite();
        $manager->persist($constructionSite);

        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }

    private function createEmptyConstructionSite(): ConstructionSite
    {
        $constructionSite = new ConstructionSite();
        $constructionSite->setName(self::EMPTY_CONSTRUCTION_SITE_NAME);
        $constructionSite->setFolderName(self::EMPTY_CONSTRUCTION_SITE_NAME);
        $constructionSite->setStreetAddress('Street');
        $constructionSite->setPostalCode(4123);
        $constructionSite->setLocality('Allschwil');
        $constructionSite->setCountry('CH');

        return $constructionSite;
    }
}
