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

use App\Entity\ConstructionManager;
use App\Tests\DataFixtures\TestUserFixtures;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticationTrait
{
    private function loginTestUser(KernelBrowser $client)
    {
        $userRepository = static::$container->get(ManagerRegistry::class)->getRepository(ConstructionManager::class);
        $testUser = $userRepository->findOneByEmail(TestUserFixtures::TEST_EMAIL);
        $client->loginUser($testUser);
    }
}
