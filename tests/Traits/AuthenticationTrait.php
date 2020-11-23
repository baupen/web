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
use App\Entity\AuthenticationToken;
use App\Entity\Base\BaseEntity;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticationTrait
{
    private function createApiTokenFor(BaseEntity $item): string
    {
        $authenticationToken = new AuthenticationToken();
        if ($item instanceof ConstructionManager) {
            $authenticationToken->setConstructionManager($item);
        } elseif ($item instanceof Craftsman) {
            $authenticationToken->setCraftsman($item);
        } elseif ($item instanceof Filter) {
            $authenticationToken->setFilter($item);
        } else {
            throw new \InvalidArgumentException('cannot create token for '.get_class($item));
        }

        $this->saveEntity($authenticationToken);

        return $authenticationToken->getToken();
    }

    private function addMap(ConstructionSite $constructionSite, string $name = 'empty'): Map
    {
        $map = new Map();
        $map->setConstructionSite($constructionSite);
        $map->setName($name);

        $this->saveEntity($map);

        return $map;
    }

    private function addCraftsman(ConstructionSite $constructionSite, string $name = 'craft'): Craftsman
    {
        $craftsman = new Craftsman();
        $craftsman->setConstructionSite($constructionSite);
        $craftsman->setContactName($name);
        $craftsman->setEmail($name.'@ch.ch');
        $craftsman->setCompany($name.' AG');
        $craftsman->setTrade($name);

        $this->saveEntity($craftsman);

        return $craftsman;
    }

    private function addIssue(ConstructionSite $constructionSite, ConstructionManager $manager): Issue
    {
        $issue = new Issue();
        $issue->setConstructionSite($constructionSite);
        $issue->setNumber(999);
        $issue->setCreatedAt(new \DateTime());
        $issue->setCreatedBy($manager);

        $this->saveEntity($issue);

        return $issue;
    }

    private function addFilter(ConstructionSite $constructionSite): Filter
    {
        $filter = new Filter();
        $filter->setConstructionSite($constructionSite);

        $this->saveEntity($filter);

        return $filter;
    }

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

    private function saveEntity(BaseEntity $entity): void
    {
        /** @var ObjectManager $manager */
        $manager = self::$container->get(ManagerRegistry::class)->getManager();
        $manager->persist($entity);
        $manager->flush();
    }
}
