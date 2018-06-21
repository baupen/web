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
use App\Entity\Map;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadMapData extends BaseFixture
{
    const ORDER = LoadConstructionSiteData::ORDER + ClearPublicUploadDir::ORDER + 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $entries = [
            ["1UG.pdf", "1UG"],
            ["2OG.pdf", "2OG", [
                ["2OG_links.pdf", "2OG rechter Bereich"],
                ["2OG_rechts.pdf", "2OG linker Bereich"],
                ["2OG_treppenhau.pdf", "2OG Treppenhaus"]
            ]],
        ];

        $constructionSite = $manager->getRepository(ConstructionSite::class)->findOneBy([]);

        foreach ($entries as $entry) {
            $this->loadMaps($manager, $constructionSite, $entry, null);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param ConstructionSite $constructionSite
     * @param array $entry
     * @param Map|null $parent
     */
    private function loadMaps(ObjectManager $manager, ConstructionSite $constructionSite, $entry, $parent)
    {
        // create map
        $map = new Map();
        $map->setConstructionSite($constructionSite);
        $map->setName($entry[1]);
        $map->setParent($parent);
        $map->setFilename(Uuid::uuid4()->toString() . ".pdf");
        $manager->persist($map);

        $this->safeCopyToPublic($map->getFilePath(), $entry[0]);

        // create children
        if (isset($entry[2])) {
            foreach ($entry[2] as $row) {
                $this->loadMaps($manager, $constructionSite, $row, $map);
            }
        }
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
