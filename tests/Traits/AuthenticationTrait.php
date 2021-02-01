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
use App\Entity\Base\BaseEntity;
use App\Entity\ConstructionManager;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticationTrait
{
    /**
     * @param \App\Entity\Traits\AuthenticationTrait $item
     */
    private function createApiTokenFor(BaseEntity $item): string
    {
        $item->setAuthenticationToken();
        $this->saveEntity($item);

        return $item->getAuthenticationToken();
    }

    private function loginApiConstructionManager(Client $client): ConstructionManager
    {
        return $this->loginConstructionManager($client->getKernelBrowser());
    }

    private function loginApiAssociatedConstructionManager(Client $client): ConstructionManager
    {
        return $this->loginUser($client->getKernelBrowser(), TestConstructionManagerFixtures::ASSOCIATED_CONSTRUCTION_MANAGER_EMAIL);
    }

    private function loginApiDisassociatedConstructionManager(Client $client): ConstructionManager
    {
        return $this->loginUser($client->getKernelBrowser(), TestConstructionManagerFixtures::DISASSOCIATED_CONSTRUCTION_MANAGER_EMAIL);
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

    private function saveEntity(BaseEntity $entity): void
    {
        /** @var ObjectManager $manager */
        $manager = self::$container->get(ManagerRegistry::class)->getManager();
        $manager->persist($entity);
        $manager->flush();
    }
}
