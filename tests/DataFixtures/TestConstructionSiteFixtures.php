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

    public const ORDER = TestUserFixtures::ORDER + 1;
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
        $userRepository = $manager->getRepository(ConstructionManager::class);
        $testUser = $userRepository->findOneBy(['email' => TestUserFixtures::TEST_EMAIL]);

        $constructionSite = $this->sampleService->createSampleConstructionSite(self::TEST_CONSTRUCTION_SITE_NAME, $testUser);
        $manager->persist($constructionSite);
        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }
}
