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
use App\Entity\Map;
use App\Entity\Craftsman;
use App\Entity\Issue;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMarkerData extends BaseFixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {

        $buildingMaps = $manager->getRepository(Map::class)->findAll();
        $craftsmen = $manager->getRepository(Craftsman::class)->findAll();
        $appUsers = $manager->getRepository(ConstructionManager::class)->findAll();

        $entries = [
            ["88286618-2247-489e-9bf4-30516ce4b201.jpg", "Laminat fehlerhaft", new \DateTime(), 0.2, 0.2, 0.4, 0.6, 0.3, 0.4],
            ["4fd59e4a-1455-35ff-86db-42188b599fda.jpg", "Steckdose eingedrückt", null, 0.1, 0.1, 0.2, 0.3, 0.15, 0.2],
            ["eb938140-4c8b-4ad2-8ed9-19963f13f749.jpg", "Löcher im Laminat", null, 0.2, 0.2, 0.6, 0.8, 0.4, 0.55],
            ["51e57029-3b5c-41da-ab7e-b65aa07bf25e.jpg", "Flecken an der Wand", null, 0.0, 0.0, 1, 1, 0.5, 0.5]
        ];

        $faker = $this->getFaker();

        foreach ($entries as $entry) {
            foreach ($buildingMaps as $buildingMap) {
                $marker = new Issue();
                $marker->setBuildingMap($buildingMap);
                $marker->setCraftsman($faker->randomElement($craftsmen));
                $marker->setImageFileName($entry[0]);
                $marker->setContent($entry[1]);
                $marker->setApproved($entry[2]);
                $marker->setCreatedBy($faker->randomElement($appUsers));

                $marker->setFrameXPercentage($entry[3]);
                $marker->setFrameYPercentage($entry[4]);
                $marker->setFrameXHeight($entry[5]);
                $marker->setFrameYLength($entry[6]);
                $marker->setMarkXPercentage($entry[7]);
                $marker->setMarkYPercentage($entry[8]);

                $manager->persist($marker);

                //move file to correct place
                $targetFile = __DIR__ . "/../../public/upload/" . $entry[0];
                if (file_exists($targetFile))
                    unlink($targetFile);
                copy(__DIR__ . "/Resources/" . $entry[0], $targetFile);
            }
        }
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * create an instance with all random values.
     *
     * @return Issue
     */
    protected function getAllRandomInstance()
    {
        $faker = $this->getFaker();

        $marker = new Issue();
        $marker->setContent($faker->text(30));
        if (rand(1, 3) == 1) {
            $marker->setApproved(new \DateTime());
        } else {
            $marker->setApproved(null);
        }
        $marker->setImageFileName("mark_image.jpg");

        $marker->setFrameXPercentage($faker->randomFloat(3, 0, 1));
        $marker->setFrameYPercentage($faker->randomFloat(3, 0, 1));
        $marker->setFrameXHeight(0.2);
        $marker->setFrameYLength(0.3);
        $marker->setMarkXPercentage($marker->getFrameXPercentage() + $marker->getFrameXHeight() * 0.5);
        $marker->setMarkYPercentage($marker->getFrameYPercentage() + $marker->getFrameYLength() * 0.5);

        return $marker;
    }
}
