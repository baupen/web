<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\ConstructionManager;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\SampleServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;

class ConstructionSiteFixtures extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = 0;

    /**
     * @var SampleServiceInterface
     */
    private $sampleService;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * ConstructionSiteFixtures constructor.
     */
    public function __construct(SampleServiceInterface $sampleService, PathServiceInterface $pathService)
    {
        $this->sampleService = $sampleService;
        $this->pathService = $pathService;
    }

    public function load(ObjectManager $manager)
    {
        // clean file system
        $folder = $this->pathService->getRootFolderOfConstructionSites();
        $fileSystem = new Filesystem();
        $fileSystem->remove($folder);
        $fileSystem->mkdir($folder);

        $constructionManagers = $manager->getRepository(ConstructionManager::class)->findAll();

        foreach (SampleServiceInterface::ALL_SAMPLES as $sample) {
            $leadConstructionManager = $constructionManagers[0];
            $constructionSite = $this->sampleService->createSampleConstructionSite($sample, $leadConstructionManager);
            foreach ($constructionManagers as $constructionManager) {
                $constructionSite->getConstructionManagers()->add($constructionManager);
                $constructionManager->getConstructionSites()->add($constructionSite);
            }

            $manager->persist($constructionSite);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return ConstructionManagerFixtures::ORDER + 1;
    }
}
