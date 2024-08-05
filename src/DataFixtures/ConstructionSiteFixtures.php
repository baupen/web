<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\SampleServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;

class ConstructionSiteFixtures extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = ConstructionManagerFixtures::ORDER + 1;

    private SampleServiceInterface $sampleService;

    private PathServiceInterface $pathService;

    /**
     * ConstructionSiteFixtures constructor.
     */
    public function __construct(SampleServiceInterface $sampleService, PathServiceInterface $pathService)
    {
        $this->sampleService = $sampleService;
        $this->pathService = $pathService;
    }

    public function load(ObjectManager $manager): void
    {
        // clean file system
        $folder = $this->pathService->getRootFolderOfConstructionSites();
        $fileSystem = new Filesystem();
        $fileSystem->remove($folder);
        $fileSystem->mkdir($folder);

        $constructionManagers = $manager->getRepository(ConstructionManager::class)->findAll();

        $constructionSites = [];
        foreach (SampleServiceInterface::ALL_SAMPLES as $sample) {
            $leadConstructionManager = $constructionManagers[0];
            $constructionSite = $this->sampleService->createSampleConstructionSite($sample, $leadConstructionManager);
            $counter = count($constructionManagers);
            for ($i = 1; $i < $counter; ++$i) {
                $constructionManager = $constructionManagers[$i];
                $constructionSite->getConstructionManagers()->add($constructionManager);
                $constructionManager->getConstructionSites()->add($constructionSite);
            }

            $this->simulateActivity($manager, $constructionSite);

            $constructionSites[] = $constructionSite;
            $manager->persist($constructionSite);
        }

        // $this->simulateManyOpenIssues($manager, $constructionSites[1]);

        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }

    private function simulateActivity(ObjectManager $manager, ConstructionSite $constructionSite): void
    {
        if ($constructionSite->getIssues()->count() < 1 || $constructionSite->getCraftsmen()->count() < 1 || $constructionSite->getConstructionManagers()->count() < 1) {
            return;
        }

        $issue = $constructionSite->getIssues()[0];
        $craftsman = $constructionSite->getCraftsmen()[0];
        $craftsman2 = $constructionSite->getCraftsmen()[1];
        $craftsman3 = $constructionSite->getCraftsmen()[2];
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
        $craftsman->setLastVisitOnline(new \DateTime('today 05:55'));
        $craftsman->setLastEmailReceived(new \DateTime('yesterday 16:15'));
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

        // issue 3: overdue
        if ($constructionSite->getIssues()->count() < 4) {
            return;
        }
        $issue = $constructionSite->getIssues()[3];
        $issue->setCraftsman($craftsman2);
        $issue->setRegisteredAt(new \DateTime('yesterday 15:00'));
        $issue->setRegisteredBy($constructionManager);
        $issue->setDeadline(new \DateTime('yesterday 16:00'));
        $manager->persist($issue);

        // issue 3: unread
        if ($constructionSite->getIssues()->count() < 5) {
            return;
        }
        $issue = $constructionSite->getIssues()[4];
        $issue->setCraftsman($craftsman3);
        $issue->setRegisteredAt(new \DateTime('yesterday 15:00'));
        $issue->setRegisteredBy($constructionManager);
        $manager->persist($issue);
    }

    //
    //    private function simulateManyOpenIssues(ObjectManager $manager, ConstructionSite $constructionSite)
    //    {
    //        /** @var Issue|false $maxIssue */
    //        $maxIssue = $constructionSite->getIssues()->last();
    //        $craftsman = $constructionSite->getCraftsmen()[0];
    //        $map = $constructionSite->getMaps()[0];
    //        $constructionManager = $constructionSite->getConstructionManagers()[0];
    //
    //        $nextNumber = $maxIssue ? $maxIssue->getNumber() : 1;
    //
    //        for ($i = $nextNumber; $i < 1000; ++$i) {
    //            $issue = new Issue();
    //            $issue->setNumber($i);
    //
    //            $issue->setConstructionSite($constructionSite);
    //            $issue->setMap($map);
    //            $issue->setCraftsman($craftsman);
    //
    //            $issue->setCreatedAt(new \DateTime());
    //            $issue->setCreatedBy($constructionManager);
    //            $issue->setRegisteredAt(new \DateTime());
    //            $issue->setRegisteredBy($constructionManager);
    //
    //            $issue->setPositionX(rand(0, 1000) / 1000);
    //            $issue->setPositionY(rand(0, 1000) / 1000);
    //            $issue->setPositionZoomScale(rand(0, 1000) / 1000);
    //
    //            $manager->persist($issue);
    //        }
    //    }
}
