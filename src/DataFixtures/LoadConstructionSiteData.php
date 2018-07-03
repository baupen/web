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
use Symfony\Component\Serializer\SerializerInterface;

class LoadConstructionSiteData extends BaseFixture
{
    const ORDER = LoadConstructionManagerData::ORDER + ClearPublicUploadDir::ORDER + 1;

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
        $json = file_get_contents(__DIR__ . '/Resources/construction_sites.json', 'r');
        /** @var ConstructionSite[] $constructionSites */
        $constructionSites = $this->serializer->deserialize($json, ConstructionSite::class . '[]', 'json');

        $appUsers = $manager->getRepository(ConstructionManager::class)->findAll();
        foreach ($constructionSites as $constructionSite) {
            $manager->persist($constructionSite);

            //copy image to correct location
            $constructionSite->setImageFilename($this->safeCopyToPublic($constructionSite->getImageFilePath(), 'construction_site_images'));

            //add user access
            foreach ($appUsers as $appUser) {
                $constructionSite->getConstructionManagers()->add($appUser);
            }

            //stop here because sample data wrongly generated
            break;
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
