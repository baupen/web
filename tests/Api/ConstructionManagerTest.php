<?php

/*
 * This file is part of the baupen project.
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
use App\Tests\Traits\FixturesTrait;
use App\Tests\Traits\TestDataTrait;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as StatusCode;

class ConstructionManagerTest extends ApiTestCase
{
    use FixturesTrait;
    use TestDataTrait;
    use AssertApiTrait;
    use AuthenticationTrait;

    public function testInvalidMethods()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);
        $testUser = $this->loginApiConstructionManager($client);

        $this->assertApiOperationUnsupported($client, '/api/construction_managers/'.$testUser->getId(), 'DELETE', 'PUT');
    }

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);

        $this->assertApiOperationNotAuthorized($client, '/api/construction_managers', 'GET');

        $userRepository = static::$container->get(ManagerRegistry::class)->getRepository(ConstructionManager::class);
        $testUser = $userRepository->findOneBy(['email' => TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL]);
        $this->assertApiOperationNotAuthorized($client, '/api/construction_managers/'.$testUser->getId(), 'GET', 'PATCH');
    }

    public function testPost()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);

        // can register
        $this->assertApiPostStatusCodeSame(StatusCode::HTTP_CREATED, $client, '/api/construction_managers', ['email' => 'test@mail.com']);
        $this->assertEmailCount(1);

        // can create other accounts if logged in fully
        $this->loginApiConstructionManager($client);
        $this->assertApiPostPayloadPersisted($client, '/api/construction_managers', ['email' => 'test2@mail.com']);
        $this->assertEmailCount(1);

        // can execute on already created accounts without error / reregistration
        $this->assertApiPostPayloadPersisted($client, '/api/construction_managers', ['email' => TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL]);
        $this->assertEmailCount(0);

        // associated construction manager does not get more info
        $this->loginApiAssociatedConstructionManager($client);
        $this->assertApiPostStatusCodeSame(StatusCode::HTTP_BAD_REQUEST, $client, '/api/construction_managers', ['email' => TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL]);
        $this->assertEmailCount(0);
    }

    public function testPatch()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);
        $ownConstructionManager = $this->loginApiConstructionManager($client);

        $sample = [
            'email' => 'patching@mail.com',
            'givenName' => 'Peter',
            'familyName' => 'Müller',
            'phone' => '0781234567',
        ];
        $privateSettings = ['receiveWeekly' => false];

        $response = $this->assertApiPostPayloadPersisted($client, '/api/construction_managers', $sample, $privateSettings);
        $newConstructionManagerId = json_decode($response->getContent(), true)['@id'];
        $this->assertApiCollectionContainsResponseItem($client, '/api/construction_managers', $response);

        $patch = [
            'givenName' => 'Dennis',
            'familyName' => 'Meier',
            'phone' => '0781234568',
            'receiveWeekly' => true,
        ];
        $this->assertApiPatchStatusCodeSame(StatusCode::HTTP_FORBIDDEN, $client, $newConstructionManagerId, $patch);
        $this->assertApiPatchPayloadPersisted($client, '/api/construction_managers/'.$ownConstructionManager->getId(), $patch);
    }

    public function testGetAuthenticationToken()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);

        $otherConstructionManagerFields = ['@id', '@type', 'givenName', 'familyName', 'email', 'phone', 'lastChangedAt'];
        $selfConstructionManagerFields = array_merge($otherConstructionManagerFields, ['authenticationToken', 'canAssociateSelf', 'receiveWeekly']);
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
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $emptyConstructionSite = $this->getEmptyConstructionSite();

        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerIri = $this->getIriFromItem($constructionManager);

        $otherConstructionManager = $this->addConstructionManager($emptyConstructionSite);
        $otherConstructionManagerIri = $this->getIriFromItem($otherConstructionManager);

        // ensure filter is applied
        $this->loginApiConstructionManager($client);
        $this->assertApiCollectionContainsIri($client, '/api/construction_managers?constructionSites.id='.$constructionSite->getId(), $constructionManagerIri);
        $this->assertApiCollectionNotContainsIri($client, '/api/construction_managers?constructionSites.id='.$emptyConstructionSite->getId(), $constructionManagerIri);

        // ensure filter is enforced for associated construction managers
        $this->loginApiAssociatedConstructionManager($client);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/construction_managers');
        $this->assertApiCollectionNotContainsIri($client, '/api/construction_managers', $otherConstructionManagerIri);
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/construction_managers?constructionSites.id='.$emptyConstructionSite->getId());
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/construction_managers?constructionSites.id='.$constructionSite->getId());
    }

    public function testLastChangedAtFilter()
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerIri = $this->getIriFromItem($constructionManager);

        $this->assertApiCollectionFilterDateTime($client, '/api/construction_managers?', $constructionManagerIri, 'lastChangedAt', $constructionManager->getLastChangedAt());
    }
}
