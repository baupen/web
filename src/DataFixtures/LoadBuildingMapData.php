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
use App\Entity\AppUser;
use App\Entity\Building;
use App\Entity\BuildingMap;
use App\Entity\Craftsman;
use App\Extension\MyTwigExtension;
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
        $buildings = $manager->getRepository(Building::class)->findAll();

        foreach ($buildings as $building) {
            $map = new BuildingMap();
            $map->setFileName("1UG.pdf");
            $map->setBuilding($building);
            $map->setName("1UG");
            $map->setDescription("Übersichtskarte vom 1UG");
            $manager->persist($map);

            $map = new BuildingMap();
            $map->setFileName("2OG_full.pdf");
            $map->setBuilding($building);
            $map->setName("2OG");
            $map->setDescription("Übersichtskarte vom 2OG");
            $manager->persist($map);

            $map = new BuildingMap();
            $map->setFileName("2OG_links.pdf");
            $map->setBuilding($building);
            $map->setName("2OG links");
            $map->setDescription("2OG linker Bereich");
            $manager->persist($map);

            $map = new BuildingMap();
            $map->setFileName("2OG_rechts.pdf");
            $map->setBuilding($building);
            $map->setName("2OG rechts");
            $map->setDescription("2OG rechter Bereich");
            $manager->persist($map);

            $map = new BuildingMap();
            $map->setFileName("2OG_treppenhaus.pdf");
            $map->setBuilding($building);
            $map->setName("2OG Treppenhaus");
            $map->setDescription("2OG Treppenhaus");
            $manager->persist($map);
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
     * @return BuildingMap
     */
    protected function getAllRandomInstance()
    {
        $buildingMap = new BuildingMap();
        $this->fillRandomThing($buildingMap);

        return $buildingMap;
    }
}
