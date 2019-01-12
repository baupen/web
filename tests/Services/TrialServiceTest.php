<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Services;

use App\Service\TrialService;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TrialServiceTest extends TestCase
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

        $request = $this->createMock(Request::class);
        $request->expects($this->any())->method('getHost')->willReturn('localhost');
        $request->expects($this->any())->method('getLocale')->willReturn('de');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->any())->method('getCurrentRequest')->willReturn($request);

        $objectManager = $this->createMock(ObjectManager::class);

        /* @noinspection PhpParamsInspection */
        $this->service = new TrialService($requestStack, $objectManager);
    }

    /**
     * check whether the new user has the trial account boolean set.
     */
    public function testCreateTrialAccount_isTrialAccountEnabled()
    {
        $constructionManager = $this->service->createTrialAccount();
        $this->assertTrue($constructionManager->getIsTrialAccount());
    }

    /**
     * check whether the new user has the trial account boolean set.
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
     */
    public function testCreateTrialAccount_namesInitialized()
    {
        $constructionManager = $this->service->createTrialAccount(null, null);
        $this->assertNotNull($constructionManager->getGivenName());
        $this->assertNotNull($constructionManager->getFamilyName());
    }

    /**
     * checks that the new user was provided some content.
     */
    public function testCreateTrialAccount_accountHasMapsAssigned()
    {
        $constructionManager = $this->service->createTrialAccount();
        $this->assertNotEmpty($constructionManager->getConstructionSites()->toArray());

        foreach ($constructionManager->getConstructionSites() as $constructionSite) {
            $this->assertNotEmpty($constructionSite->getMaps()->toArray());
        }
    }
}
