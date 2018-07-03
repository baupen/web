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

use App\DataFixtures\Base\BaseFixture;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadConstructionSiteData extends BaseFixture
{
    const ORDER = LoadConstructionManagerData::ORDER + ClearPublicUploadDir::ORDER + 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $entries = [];

        $handle = fopen(__DIR__ . '/Resources/construction_sites.csv', 'r');
        while ($handle && ($data = fgetcsv($handle, null, ',')) !== false) {
            if (count($data) >= 10 && $data[0] !== '') {
                $entries[] = [$data[0], $data[1], $data[2], $data[3], $data[4]];
            }
        }

        $entries = [
            ['Sun Park', 'Parkstrasse 12', 7270, 'Davos', 'CH', 'preview.jpg'],
            ['Sun Park (empty)', 'Parkstrasse 12', 7270, 'Davos', 'CH', 'preview2.jpg'],
        ];

        $appUsers = $manager->getRepository(ConstructionManager::class)->findAll();
        foreach ($entries as $entry) {
            $building = new ConstructionSite();

            $building->setImageFileName(Uuid::uuid4()->toString() . '.jpg');
            $building->setName($entry[0]);
            $building->setStreetAddress($entry[1]);
            $building->setPostalCode($entry[2]);
            $building->setLocality($entry[3]);
            $building->setCountry($entry[4]);
            $manager->persist($building);

            $this->safeCopyToPublic($building->getImageFilePath(), 'construction_site_images', $entry[5]);

            foreach ($appUsers as $appUser) {
                $building->getConstructionManagers()->add($appUser);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
