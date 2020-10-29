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
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticationTrait
{
    private function loginApiConstructionManagerExternal(Client $client): ConstructionManager
    {
        return $this->loginUser($client->getKernelBrowser(), TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EXTERNAL_EMAIL);
    }

    private function loginApiConstructionManagerTrial(Client $client): ConstructionManager
    {
        return $this->loginUser($client->getKernelBrowser(), TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_TRIAL_EMAIL);
    }

    private function loginApiConstructionManager(Client $client): ConstructionManager
    {
        return $this->loginConstructionManager($client->getKernelBrowser());
    }

    private function loginConstructionManager(KernelBrowser $client): ConstructionManager
    {
        return $this->loginUser($client, TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL);
    }

    private function loginUser(KernelBrowser $client, string $email): ConstructionManager
    {
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::$container->get(ManagerRegistry::class);
        $userRepository = $managerRegistry->getRepository(ConstructionManager::class);
        $testUser = $userRepository->findOneBy(['email' => $email]);

        if (!$testUser) {
            throw new \Exception('User for E-Mail '.$email.' not found. Likely you need to load the '.TestConstructionManagerFixtures::class.' fixture first.');
        }

        $client->loginUser($testUser);

        return $testUser;
    }
}
