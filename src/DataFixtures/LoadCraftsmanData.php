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
use App\Entity\Craftsman;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class LoadCraftsmanData extends BaseFixture
{
    const ORDER = SimulateServerDirectoryStructure::ORDER + LoadConstructionManagerData::ORDER + 1;

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
        $json = file_get_contents(__DIR__ . '/Resources/craftsmen.json');
        $counter = 0;

        $constructionSites = $manager->getRepository(ConstructionSite::class)->findAll();
        foreach ($constructionSites as $constructionSite) {
            /** @var Craftsman[] $craftsmen */
            $craftsmen = [];
            foreach ($this->serializer->deserialize($json, Craftsman::class . '[]', 'json') as $craftsman) {
                /* @var Craftsman $craftsman */
                $craftsman->setEmail($craftsman->getEmail() . '.example.com');
                $craftsmen[] = $craftsman;
            }
            $craftsmen = array_merge($craftsmen, $this->getConstructionSiteCraftsmen($manager));
            foreach ($craftsmen as $craftsman) {
                $craftsman->setConstructionSite($constructionSite);
                $craftsman->setEmailIdentifier();
                if ($counter++ % 3 === 0) {
                    $craftsman->setLastOnlineVisit(new \DateTime());
                    $craftsman->setLastEmailSent(new \DateTime());
                }
                $manager->persist($craftsman);
                $manager->flush();
                $manager->persist($craftsman->setShareViewFilter());
            }
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return array
     */
    private function getConstructionSiteCraftsmen(ObjectManager $manager)
    {
        $craftsmen = [];
        foreach ($manager->getRepository(ConstructionManager::class)->findAll() as $constructionManager) {
            $craftsman = new Craftsman();
            $craftsman->setEmail($constructionManager->getEmail());
            $craftsman->setCompany('mangel.io');
            $craftsman->setTrade('Programmierung');
            $craftsman->setContactName($constructionManager->getName());
            $craftsmen[] = $craftsman;
        }

        return $craftsmen;
    }

    public function getOrder()
    {
        return static::ORDER + 1;
    }
}
