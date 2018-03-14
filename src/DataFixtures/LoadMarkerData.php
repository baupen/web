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
use App\Entity\BuildingMap;
use App\Entity\Craftsman;
use App\Entity\Marker;
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

        $faker = $this->getFaker();
        $craftsman = $manager->getRepository(Craftsman::class)->findAll();
        $buildingMaps = $manager->getRepository(BuildingMap::class)->findAll();
        $appUsers = $manager->getRepository(AppUser::class)->findAll();

        foreach ($buildingMaps as $buildingMap) {
            /* @var Marker[] $markers */
            $markers = $this->loadSomeRandoms($manager);

            foreach ($markers as $marker) {
                $marker->setCraftsman($faker->randomElement($craftsman));
                $marker->setBuildingMap($buildingMap);
                $marker->setCreatedBy($faker->randomElement($appUsers));
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
     * @return Marker
     */
    protected function getAllRandomInstance()
    {
        $faker = $this->getFaker();

        $marker = new Marker();
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
