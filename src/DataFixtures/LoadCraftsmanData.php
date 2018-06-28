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
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCraftsmanData extends BaseFixture
{
    const ORDER = LoadConstructionSiteData::ORDER + 1;

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

        $handle = fopen(__DIR__ . '/Resources/craftsmen_list.csv', 'r');
        while (($data = fgetcsv($handle, null, ',')) !== false) {
            if (count($data) >= 10 && $data[0] !== '') {
                $entries[] = [$data[1], $data[2], $data[3], $data[10]];
            }
        }

        $constructionSite = $manager->getRepository(ConstructionSite::class)->findOneBy([]);

        foreach ($entries as $data) {
            $craftsman = new Craftsman();
            $craftsman->setConstructionSite($constructionSite);
            $craftsman->setTrade($data[0]);
            $craftsman->setCompany($data[1]);
            $craftsman->setContactName($data[2]);
            $craftsman->setEmail($data[3] . '.example.com');
            $manager->persist($craftsman);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return static::ORDER + 1;
    }
}
