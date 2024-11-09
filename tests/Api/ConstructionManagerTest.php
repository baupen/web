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

class ConstructionManagerTest extends ApiTestCase
{
    use FixturesTrait;
    use TestDataTrait;
    use AssertApiTrait;
    use AuthenticationTrait;

    public function testInvalidMethods(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);
        $testUser = $this->loginApiConstructionManager($client);

        $this->assertApiOperationUnsupported($client, '/api/construction_managers/'.$testUser->getId(), 'DELETE', 'PUT');
    }

    public function testValidMethodsNeedAuthentication(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);

        $this->assertApiOperationNotAuthorized($client, '/api/construction_managers', 'GET');

        $userRepository = static::getClient()->getContainer()->get(ManagerRegistry::class)->getRepository(ConstructionManager::class);
        $testUser = $userRepository->findOneBy(['email' => TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL]);
        $this->assertApiOperationNotAuthorized($client, '/api/construction_managers/'.$testUser->getId(), 'GET', 'PATCH');
    }

    public function testPost(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);

        // can register
        $this->assertApiPostStatusCodeSame(Response::HTTP_CREATED, $client, '/api/construction_managers', ['email' => 'test@mail.com']);
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
        $this->assertApiPostStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/construction_managers', ['email' => TestConstructionManagerFixtures::CONSTRUCTION_MANAGER_EMAIL]);
        $this->assertEmailCount(0);
    }

    public function testPatch(): void
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
        $this->assertApiPatchStatusCodeSame(Response::HTTP_FORBIDDEN, $client, $newConstructionManagerId, $patch);
        $this->assertApiPatchPayloadPersisted($client, '/api/construction_managers/'.$ownConstructionManager->getId(), $patch);
    }

    public function testGetAuthenticationToken(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);

        $otherConstructionManagerFields = ['@id', '@type', 'givenName', 'familyName', 'isEnabled', 'email', 'phone', 'lastChangedAt'];
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

    public function testConstructionSiteFilters(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $emptyConstructionSite = $this->getEmptyConstructionSite();

        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerIri = $this->getIriFromItem($constructionManager);

        $emptyManager = $this->addConstructionManager($emptyConstructionSite);
        $emptyManagerIri = $this->getIriFromItem($emptyManager);

        // ensure filter is applied
        $this->loginApiConstructionManager($client);
        $this->assertApiCollectionContainsIri($client, '/api/construction_managers?constructionSites.id='.$constructionSite->getId(), $constructionManagerIri);
        $this->assertApiCollectionNotContainsIri($client, '/api/construction_managers?constructionSites.id='.$emptyConstructionSite->getId(), $constructionManagerIri);

        // ensure associated construction manager cannot access empty construction site
        $this->loginApiAssociatedConstructionManager($client);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/construction_managers');
        $this->assertApiCollectionNotContainsIri($client, '/api/construction_managers', $emptyManagerIri);
        $this->assertApiGetStatusCodeSame(Response::HTTP_FORBIDDEN, $client, $emptyManagerIri);
        $this->assertApiGetStatusCodeSame(Response::HTTP_BAD_REQUEST, $client, '/api/construction_managers?constructionSites.id='.$emptyConstructionSite->getId());
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/construction_managers?constructionSites.id='.$constructionSite->getId());
    }

    public function testConstructionSiteFiltersWithDisassociatedConstructionManagers(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();

        $emptyConstructionSite = $this->getEmptyConstructionSite();
        $newManager = $this->addConstructionManager($emptyConstructionSite);
        $newManagerIri = $this->getIriFromItem($newManager);

        $this->addIssue($constructionSite, $newManager);

        // ensure associated construction manager gets access to all relevant construction managers
        // note: newManager never added to constructionSite, but still returned, as has an issue created for it
        $this->loginApiAssociatedConstructionManager($client);
        $this->assertApiGetStatusCodeSame(Response::HTTP_OK, $client, '/api/construction_managers/'.$newManager->getId());
        $this->assertApiCollectionContainsIri($client, '/api/construction_managers', $newManagerIri);
        $this->assertApiCollectionContainsIri($client, '/api/construction_managers?constructionSites.id='.$constructionSite->getId(), $newManagerIri);
    }

    public function testLastChangedAtFilter(): void
    {
        $client = $this->createClient();
        $this->loadFixtures($client, [TestConstructionManagerFixtures::class]);
        $constructionManager = $this->loginApiConstructionManager($client);
        $constructionManagerIri = $this->getIriFromItem($constructionManager);

        $this->assertApiCollectionFilterDateTime($client, '/api/construction_managers?', $constructionManagerIri, 'lastChangedAt', $constructionManager->getLastChangedAt());
    }
}
