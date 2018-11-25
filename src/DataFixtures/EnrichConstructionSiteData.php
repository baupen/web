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
use App\Service\Interfaces\PathServiceInterface;
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
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $json = file_get_contents(__DIR__ . \DIRECTORY_SEPARATOR . 'Resources' . \DIRECTORY_SEPARATOR . 'construction_sites.json');
        /** @var ConstructionSite[] $rawConstructionSites */
        $rawConstructionSites = $this->serializer->deserialize($json, ConstructionSite::class, 'json');

        /** @var ConstructionSite[] $constructionSiteLookup */
        $constructionSiteLookup = [];
        foreach ($rawConstructionSites as $rawConstructionSite) {
            $constructionSiteLookup[$rawConstructionSite->getFolderName()] = $rawConstructionSite->getFolderName();
        }

        $constructionSites = $manager->getRepository(ConstructionSite::class)->findAll();
        foreach ($constructionSites as $constructionSite) {
            if (array_key_exists($constructionSite->getFolderName(), $constructionSiteLookup)) {
                $source = $constructionSiteLookup[$constructionSite->getFolderName()];

                $constructionSite->setName($source->getName());
                $constructionSite->setStreetAddress($source->getStreetAddress());
                $constructionSite->setPostalCode($source->getPostalCode());
                $constructionSite->setLocality($source->getLocality());
                $constructionSite->setCountry($source->getCountry());

                $manager->persist($constructionSite);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
