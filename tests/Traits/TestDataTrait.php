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
    private function getTestConstructionSite(): ConstructionSite
    {
        /** @var ManagerRegistry $registry */
        $registry = static::$container->get(ManagerRegistry::class);
        $constructionSiteRepository = $registry->getRepository(ConstructionSite::class);

        return $constructionSiteRepository->findOneBy(['name' => TestConstructionSiteFixtures::TEST_CONSTRUCTION_SITE_NAME]);
    }
}
