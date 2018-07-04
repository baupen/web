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
use Symfony\Component\Serializer\SerializerInterface;

class LoadMapData extends BaseFixture
{
    const ORDER = LoadConstructionSiteData::ORDER + ClearPublicUploadDir::ORDER + 1;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $json = file_get_contents(__DIR__ . '/Resources/maps.json', 'r');
        $rawMaps = json_decode($json);

        $constructionSites = $manager->getRepository(ConstructionSite::class)->findAll();
        foreach ($constructionSites as $constructionSite) {
            $this->loadMaps($manager, $constructionSite, $rawMaps, null);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param ConstructionSite $constructionSite
     * @param array $rawMaps
     * @param Map|null $parent
     */
    private function loadMaps(ObjectManager $manager, ConstructionSite $constructionSite, $rawMaps, $parent)
    {
        foreach ($rawMaps as $rawMap) {
            //create map
            $map = new Map();
            $map->setConstructionSite($constructionSite);
            $map->setName($rawMap->name);
            $map->setParent($parent);

            //copy image to correct location
            if ($rawMap->filename !== null) {
                $map->setFilename($rawMap->filename);
                $map->setFilename($this->safeCopyToPublic($map->getFilePath(), 'maps'));
            }

            //create children
            if (property_exists($rawMap, 'children')) {
                $this->loadMaps($manager, $constructionSite, $rawMap->children, $map);
            }

            $manager->persist($map);
        }
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
