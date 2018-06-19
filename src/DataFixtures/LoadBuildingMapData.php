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
use App\Entity\ConstructionSite;
use App\Entity\Map;
use Doctrine\Common\Persistence\ObjectManager;

class LoadBuildingMapData extends BaseFixture
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
        $buildings = $manager->getRepository(ConstructionSite::class)->findAll();

        $entries = [
            ["d320e74c-cf71-451c-805e-eadd4920f073.pdf", "1UG", "Übersichtskarte vom 1UG"],
            ["91209286-75f2-4cf1-a8e8-5b9e7d357914.pdf", "2OG", "Übersichtskarte vom 2OG"],
            ["f7032a13-e6d6-4fa6-ab43-060f5422d384.pdf", "2OG rechts", "2OG rechter Bereich"],
            ["7f3fd26d-9f32-47a7-a738-3bbbf262f432.pdf", "2OG links", "2OG linker Bereich"],
            ["3384138c-ea50-4f92-a72a-05bf7918518a.pdf", "2OG Treppen", "2OG Treppenhaus"],
        ];

        foreach ($entries as $entry) {
            foreach ($buildings as $building) {
                $map = new Map();
                $map->setBuilding($building);
                $map->setFilename($entry[0]);
                $map->setName($entry[1]);
                $map->setDescription($entry[2]);
                $map->publish();
                $manager->persist($map);

                //move file to correct place
                $targetFile = __DIR__ . "/../../public/upload/" . $entry[0];
                if (file_exists($targetFile))
                    unlink($targetFile);
                copy(__DIR__ . "/Resources/" . $entry[0], $targetFile);

            }
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 15;
    }

    /**
     * create an instance with all random values.
     *
     * @return Map
     */
    protected function getAllRandomInstance()
    {
        $buildingMap = new Map();
        $this->fillRandomThing($buildingMap);

        return $buildingMap;
    }
}
