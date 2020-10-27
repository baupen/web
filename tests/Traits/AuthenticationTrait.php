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

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\ConstructionManager;
use App\Tests\DataFixtures\TestUserFixtures;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticationTrait
{
    private function loginTestUser(KernelBrowser $client): ConstructionManager
    {
        $userRepository = static::$container->get(ManagerRegistry::class)->getRepository(ConstructionManager::class);
        $testUser = $userRepository->findOneByEmail(TestUserFixtures::TEST_EMAIL);

        if (!$testUser) {
            throw new \Exception('You need to load the '.TestUserFixtures::class.' fixture first.');
        }

        $client->loginUser($testUser);

        return $testUser;
    }

    private function loginApiTestUser(Client $client): ConstructionManager
    {
        return $this->loginTestUser($client->getKernelBrowser());
    }
}
