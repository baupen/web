<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Map;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadIssueData extends BaseFixture
{
    const ORDER = LoadConstructionSiteData::ORDER + LoadConstructionManagerData::ORDER + LoadCraftsmanData::ORDER + 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $maps = $manager->getRepository(Map::class)->findAll();
        $craftsmen = $manager->getRepository(Craftsman::class)->findAll();
        $constructionManager = $manager->getRepository(ConstructionManager::class)->findOneBy([]);
        $constructionSite = $manager->getRepository(ConstructionSite::class)->findOneBy([]);

        $entries = [
            ["parkett.jpg", "Laminat fehlerhaft", true, false, 0.8, 0.3, 0.5, 2],
            ["steckdose.jpg", "Steckdose eingedrückt", false, false, 0.1, 0.1, 0.2, 3],
            ["parkett2.jpg", "Löcher im Parkett", false, false, 0.2, 0.2, 0.6, 3],
            ["farbfleck.jpg", "Flecken an der Wand", false, false, 0.5, 0.3, 1, 1]
        ];

        $faker = $this->getFaker();

        $craftsmanIndex = 0;
        $getCraftsman = function () use (&$craftsmanIndex, $craftsmen) {
            $res = $craftsmen[$craftsmanIndex++];
            if ($craftsmanIndex >= count($craftsmen)) {
                $craftsmanIndex = 0;
            }
            return $res;
        };

        $numberCount = 1;
        foreach ($entries as $entry) {
            foreach ($maps as $map) {
                $craftsman = $getCraftsman();
                $targetFileName = $this->safeCopyToPublic("/upload/" . $constructionSite->getId() . "/issue", $entry[0], "jpg");

                $issue = new Issue();
                $issue->setCraftsman($craftsman);
                $issue->setImageFilename($targetFileName);
                $issue->setDescription($entry[1]);
                $issue->setIsMarked($entry[2]);
                $issue->setWasAddedWithClient($entry[3]);
                $issue->setPositionX($entry[4]);
                $issue->setPositionY($entry[5]);
                $issue->setPositionZoomScale($entry[6]);
                $issue->setNumber($numberCount++);
                $issue->setMap($map);

                $issue->setUploadBy($constructionManager);
                $issue->setUploadedAt(new \DateTime("-10 hours"));

                switch ($entry[7]) {
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 3:
                        $issue->setReviewBy($constructionManager);
                        $issue->setReviewedAt(new \DateTime());
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 2:
                        $issue->setResponseBy($craftsman);
                        $issue->setRespondedAt(new \DateTime("-2 hours"));
                    case 1:
                        $issue->setRegistrationBy($constructionManager);
                        $issue->setRegisteredAt(new \DateTime("-5 hours"));
                }

                $manager->persist($issue);
            }
        }
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return static::ORDER;
    }
}
