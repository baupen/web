<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Services;

use App\Entity\ConstructionManager;
use App\Service\Interfaces\EmailServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\UserService;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testWhitelist(): void
    {
        $mockPathService = $this->getMockForAbstractClass(PathServiceInterface::class);

        $mockPathService->method('getTransientFolderForAuthorization')
            ->willReturn(__DIR__.DIRECTORY_SEPARATOR.'UserServiceTest');

        $mockManagerRegistry = $this->createMock(ManagerRegistry::class);
        $mockEmailService = $this->createMock(EmailServiceInterface::class);
        $userService = new UserService($mockPathService, $mockManagerRegistry, $mockEmailService, 'whitelist');

        $constructionManager = new ConstructionManager();

        $constructionManager->setEmail('peter@invalid.ch');
        $userService->authorize($constructionManager);
        $this->assertFalse($constructionManager->getCanAssociateSelf());

        $constructionManager->setEmail('valid@house.ch');
        $userService->authorize($constructionManager);
        $this->assertTrue($constructionManager->getCanAssociateSelf());
        $this->assertTrue($constructionManager->getIsEnabled());

        $constructionManager->setEmail('other_valid@peter.ch');
        $userService->authorize($constructionManager);
        $this->assertTrue($constructionManager->getCanAssociateSelf());
        $this->assertTrue($constructionManager->getIsEnabled());

        $constructionManager->setEmail('something@validdomain.ch');
        $userService->authorize($constructionManager);
        $this->assertTrue($constructionManager->getCanAssociateSelf());
        $this->assertTrue($constructionManager->getIsEnabled());

        $constructionManager->setEmail('something_entirely_different@validdomain2.ch');
        $userService->authorize($constructionManager);
        $this->assertTrue($constructionManager->getCanAssociateSelf());
        $this->assertTrue($constructionManager->getIsEnabled());

        $constructionManager->setEmail('invalid@invalid.ch');
        $userService->authorize($constructionManager);
        $this->assertFalse($constructionManager->getIsEnabled());
    }
}
