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
use App\Entity\ConstructionSite;
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

            $this->simulateActivity($manager, $constructionSite);

            $manager->persist($constructionSite);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return ConstructionManagerFixtures::ORDER + 1;
    }

    private function simulateActivity(ObjectManager $manager, ConstructionSite $constructionSite)
    {
        if ($constructionSite->getIssues()->count() < 1 || $constructionSite->getCraftsmen()->count() < 1 || $constructionSite->getConstructionManagers()->count() < 1) {
            return;
        }

        $issue = $constructionSite->getIssues()[0];
        $craftsman = $constructionSite->getCraftsmen()[0];
        $constructionManager = $constructionSite->getConstructionManagers()[0];

        // issue 1: open
        $issue->setCraftsman($craftsman);
        $issue->setRegisteredAt(new \DateTime('yesterday 15:00'));
        $issue->setRegisteredBy($constructionManager);
        $manager->persist($issue);

        if ($constructionSite->getIssues()->count() < 2) {
            return;
        }

        // issue 2: resolved
        $issue = $constructionSite->getIssues()[1];
        $issue->setCraftsman($craftsman);
        $issue->setRegisteredAt(new \DateTime('yesterday 15:00'));
        $issue->setRegisteredBy($constructionManager);
        $issue->setResolvedAt(new \DateTime('today 06:00'));
        $issue->setResolvedBy($craftsman);
        $craftsman->setLastOnlineVisit(new \DateTime('today 05:55'));
        $craftsman->setLastEmailSent(new \DateTime('yesterday 16:15'));
        $manager->persist($issue);
        $manager->persist($craftsman);

        // issue 3: closed
        if ($constructionSite->getIssues()->count() < 3) {
            return;
        }
        $issue = $constructionSite->getIssues()[2];
        $issue->setCraftsman($craftsman);
        $issue->setRegisteredAt(new \DateTime('yesterday 15:00'));
        $issue->setRegisteredBy($constructionManager);
        $issue->setClosedAt(new \DateTime('today 08:00'));
        $issue->setClosedBy($constructionManager);
        $manager->persist($issue);
    }
}
