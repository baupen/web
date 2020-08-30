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
use BadMethodCallException;
use Doctrine\Common\Persistence\ObjectManager;

class EnrichMapsData extends BaseFixture
{
    const ORDER = SetupContentFolders::ORDER + ClearContentFolders::ORDER + LoadIssueData::ORDER + 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @throws BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $constructionSites = $manager->getRepository(ConstructionSite::class)->findAll();

        foreach ($constructionSites as $constructionSite) {
            $map = new Map();
            $map->setName('empty map');

            $map2 = new Map();
            $map2->setName('child of empty map');
            $map2->setConstructionSite($constructionSite);
            $map2->setParent($map);
            $map->getChildren()->add($map2);
            $manager->persist($map2);

            $map->setConstructionSite($constructionSite);
            $constructionSite->getMaps()->add($map);
            $manager->persist($map);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
