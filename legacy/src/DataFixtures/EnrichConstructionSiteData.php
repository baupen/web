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
use App\Service\Interfaces\PathServiceInterface;
use BadMethodCallException;
use const DIRECTORY_SEPARATOR;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class EnrichConstructionSiteData extends BaseFixture
{
    const ORDER = SetupContentFolders::ORDER + ClearContentFolders::ORDER + 1;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    public function __construct(SerializerInterface $serializer, PathServiceInterface $pathService)
    {
        $this->serializer = $serializer;
        $this->pathService = $pathService;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @throws BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $constructionSites = $manager->getRepository(ConstructionSite::class)->findAll();
        $constructionSiteManagers = $manager->getRepository(ConstructionManager::class)->findAll();

        /** @var ConstructionSite[] $constructionSiteLookup */
        $constructionSiteLookup = [];
        foreach ($constructionSites as $constructionSite) {
            $constructionSiteLookup[$constructionSite->getFolderName()] = $constructionSite;
        }

        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'construction_sites.json');
        /** @var ConstructionSite[] $rawConstructionSites */
        $rawConstructionSites = $this->serializer->deserialize($json, ConstructionSite::class . '[]', 'json');

        foreach ($rawConstructionSites as $rawConstructionSite) {
            $key = $rawConstructionSite->getFolderName();
            if (\array_key_exists($key, $constructionSiteLookup)) {
                $constructionSite = $constructionSiteLookup[$key];

                // only add managers to already existing construction sites because only those are filled with data
                foreach ($constructionSiteManagers as $constructionSiteManager) {
                    $constructionSite->getConstructionManagers()->add($constructionSiteManager);
                }
            } else {
                $constructionSite = new ConstructionSite();
                $constructionSite->setFolderName($rawConstructionSite->getFolderName());
            }

            // set enriched data
            $constructionSite->setName($rawConstructionSite->getName());
            $constructionSite->setStreetAddress($rawConstructionSite->getStreetAddress());
            $constructionSite->setPostalCode($rawConstructionSite->getPostalCode());
            $constructionSite->setLocality($rawConstructionSite->getLocality());
            $constructionSite->setCountry($rawConstructionSite->getCountry());

            $manager->persist($constructionSite);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
