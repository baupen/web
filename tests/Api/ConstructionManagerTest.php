<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ConstructionManager;
use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use App\Tests\Traits\TestDataTrait;
use Doctrine\Persistence\ManagerRegistry;
use Liip\TestFixturesBundle\Test\FixturesTrait;

class ConstructionManagerTest extends ApiTestCase
{
    use FixturesTrait;
    use TestDataTrait;
    use AssertApiTrait;
    use AuthenticationTrait;

    public function testInvalidMethods()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class]);
        $testUser = $this->loginApiConstructionManager($client);

        $this->assertApiOperationUnsupported($client, '/api/construction_managers', 'POST');
        $this->assertApiOperationUnsupported($client, '/api/construction_managers/'.$testUser->getId(), 'DELETE', 'PUT', 'PATCH');
    }

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class]);

        $this->assertApiOperationNotAuthorized($client, '/api/construction_managers', 'GET');

        $userRepository = static::$container->get(ManagerRegistry::class)->getRepository(ConstructionManager::class);
        $testUser = $userRepository->findOneByEmail(TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL);
        $this->assertApiOperationNotAuthorized($client, '/api/construction_managers/'.$testUser->getId(), 'GET');
    }

    public function testGetAuthenticationToken()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);

        $otherConstructionManagerFields = ['@id', '@type', 'givenName', 'familyName', 'email', 'phone'];
        $selfConstructionManagerFields = array_merge($otherConstructionManagerFields, ['authenticationToken']);
        sort($otherConstructionManagerFields);
        sort($selfConstructionManagerFields);

        $constructionManagerIri = $this->getIriFromItem($constructionManager);
        $response = $this->assertApiGetOk($client, '/api/construction_managers');
        $constructionManagers = json_decode($response->getContent(), true);
        foreach ($constructionManagers['hydra:member'] as $constructionManager) {
            $actualFields = array_keys($constructionManager);
            sort($actualFields);
            if ($constructionManager['@id'] === $constructionManagerIri) {
                $this->assertArraySubset($actualFields, $selfConstructionManagerFields);
            } else {
                $this->assertArraySubset($actualFields, $otherConstructionManagerFields);
            }
        }
    }

    public function testConstructionSiteFilters()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerIri = $this->getIriFromItem($constructionManager);

        $constructionSite = $this->getTestConstructionSite();
        $emptyConstructionSite = $this->getEmptyConstructionSite();

        $this->assertApiCollectionContainsIri($client, '/api/construction_managers?constructionSites.id='.$constructionSite->getId(), $constructionManagerIri);
        $this->assertApiCollectionNotContainsIri($client, '/api/construction_managers?constructionSites.id='.$emptyConstructionSite->getId(), $constructionManagerIri);
    }
}
