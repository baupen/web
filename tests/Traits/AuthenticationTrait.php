<?php

namespace App\Tests\Traits;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Base\BaseEntity;
use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Helper\DoctrineHelper;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticationTrait
{
    private function createApiTokenFor(ConstructionManager|Craftsman|Filter $item): string
    {
        $item->setAuthenticationToken();
        $this->saveEntity($item);

        return $item->getAuthenticationToken();
    }

    private function loginApiConstructionManager(Client $client): ConstructionManager
    {
        return $this->loginApiSpecificConstructionManager($client, TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL);
    }

    private function loginApiAssociatedConstructionManager(Client $client): ConstructionManager
    {
        return $this->loginApiSpecificConstructionManager($client, TestConstructionManagerFixtures::ASSOCIATED_CONSTRUCTION_MANAGER_EMAIL);
    }

    private function loginApiDisassociatedConstructionManager(Client $client): ConstructionManager
    {
        return $this->loginApiSpecificConstructionManager($client, TestConstructionManagerFixtures::DISASSOCIATED_CONSTRUCTION_MANAGER_EMAIL);
    }

    private function loginConstructionManager(KernelBrowser $client, bool $setHeader = false): ConstructionManager
    {
        return $this->loginSpecificConstructionManager($client, TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL, $setHeader);
    }

    private function loginApiSpecificConstructionManager(Client $client, string $email): ConstructionManager
    {
        $testUser = $this->getConstructionManagerByEmail($email);
        $client->setDefaultOptions(['headers' => ['X-AUTHENTICATION' => [$testUser->getAuthenticationToken()]]]);

        return $testUser;
    }

    private function loginSpecificConstructionManager(KernelBrowser $client, string $email, bool $setHeader = false): ConstructionManager
    {
        $testUser = $this->getConstructionManagerByEmail($email);
        $client->loginUser($testUser);
        if ($setHeader) {
            $client->setServerParameter('HTTP_X-AUTHENTICATION', $testUser->getAuthenticationToken());
        }

        return $testUser;
    }

    private function loginApiCraftsman(Client $client, Craftsman $craftsman): void
    {
        $client->setDefaultOptions(['headers' => ['X-AUTHENTICATION' => [$craftsman->getAuthenticationToken()]]]);
    }

    private function loginApiFilter(Client $client, Filter $filter): void
    {
        $client->setDefaultOptions(['headers' => ['X-AUTHENTICATION' => [$filter->getAuthenticationToken()]]]);
    }

    private function saveEntity(...$entities): void
    {
        $registry = static::getClient()->getContainer()->get(ManagerRegistry::class);
        DoctrineHelper::persistAndFlush($registry, ...$entities);
    }

    private function reloadEntity(BaseEntity $entity): BaseEntity
    {
        /** @var ObjectManager $manager */
        $manager = static::getClient()->getContainer()->get(ManagerRegistry::class)->getManager();

        return $manager->getRepository(get_class($entity))->find($entity->getId());
    }
}
