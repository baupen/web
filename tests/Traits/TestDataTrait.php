<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits;

use App\Entity\ConstructionSite;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use Doctrine\Persistence\ManagerRegistry;

trait TestDataTrait
{
    private function getIriFromItem($item)
    {
        return static::$container->get('api_platform.iri_converter')->getIriFromItem($item);
    }

    private function getTestConstructionSite(): ConstructionSite
    {
        return $this->getConstructionSiteByName(TestConstructionSiteFixtures::TEST_CONSTRUCTION_SITE_NAME);
    }

    private function getEmptyConstructionSite(): ConstructionSite
    {
        return $this->getConstructionSiteByName(TestConstructionSiteFixtures::EMPTY_CONSTRUCTION_SITE_NAME);
    }

    private function getConstructionSiteByName(string $constructionSiteName): ConstructionSite
    {
        /** @var ManagerRegistry $registry */
        $registry = static::$container->get(ManagerRegistry::class);
        $constructionSiteRepository = $registry->getRepository(ConstructionSite::class);

        return $constructionSiteRepository->findOneBy(['name' => $constructionSiteName]);
    }
}
