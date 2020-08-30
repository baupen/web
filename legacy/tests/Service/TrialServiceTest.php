<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Service;

use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\SyncServiceInterface;
use App\Service\TrialService;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrialServiceTest extends WebTestCase
{
    /**
     * @var TrialService
     */
    private $service;

    /**
     * TrialServiceTest constructor.
     *
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        self::bootKernel();
        /** @var PathServiceInterface $pathService */
        $pathService = self::$container->get(PathServiceInterface::class);
        /** @var TranslatorInterface $translator */
        $translator = self::$container->get(TranslatorInterface::class);
        /** @var SyncServiceInterface $syncService */
        $syncService = self::$container->get(SyncServiceInterface::class);
        /** @var RegistryInterface $objectManager */
        $objectManager = self::$container->get(RegistryInterface::class);
        $requestStack = $this->createRequestStackMock();

        $this->service = new TrialService($pathService, $translator, $syncService, $requestStack, $objectManager);
    }

    /**
     * @return MockObject|RequestStack
     */
    private function createRequestStackMock()
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->any())->method('getHost')->willReturn('localhost');
        $request->expects($this->any())->method('getLocale')->willReturn('de');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->any())->method('getCurrentRequest')->willReturn($request);

        return $requestStack;
    }

    /**
     * check whether the new user has the trial account boolean set.
     *
     * @throws Exception
     */
    public function testCreateTrialAccount_isTrialAccountEnabled()
    {
        $constructionManager = $this->service->createTrialAccount();
        $this->assertTrue($constructionManager->getIsTrialAccount());
    }

    /**
     * check whether the new user has the trial account boolean set.
     *
     * @throws Exception
     */
    public function testCreateTrialAccount_useProposedNames()
    {
        $givenName = 'Anna';
        $familyName = 'Schweigert';

        $constructionManager = $this->service->createTrialAccount($givenName, $familyName);
        $this->assertSame($givenName, $constructionManager->getGivenName());
        $this->assertSame($familyName, $constructionManager->getFamilyName());
    }

    /**
     * check whether the new user has the trial account boolean set.
     *
     * @throws Exception
     */
    public function testCreateTrialAccount_namesInitialized()
    {
        $constructionManager = $this->service->createTrialAccount(null, null);
        $this->assertNotNull($constructionManager->getGivenName());
        $this->assertNotNull($constructionManager->getFamilyName());
    }

    /**
     * checks that the new user was provided some content.
     *
     * @throws Exception
     */
    public function testCreateTrialAccount_accountHasMapsAssigned()
    {
        $constructionManager = $this->service->createTrialAccount();
        $this->assertNotEmpty($constructionManager->getConstructionSites()->toArray());

        foreach ($constructionManager->getConstructionSites() as $constructionSite) {
            $this->assertNotEmpty($constructionSite->getMaps()->toArray());
        }
    }

    /**
     * checks that the new user was provided some content.
     *
     * @throws Exception
     */
    public function testCreateTrialAccount_accountHasPreviewImagesAssigned()
    {
        $constructionManager = $this->service->createTrialAccount();
        $this->assertNotEmpty($constructionManager->getConstructionSites()->toArray());

        foreach ($constructionManager->getConstructionSites() as $constructionSite) {
            $this->assertNotEmpty($constructionSite->getMaps()->toArray());
            $this->assertNotEmpty($constructionSite->getImages()->toArray());
        }
    }
}
