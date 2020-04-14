<?php


namespace App\Tests\Service;


use App\Entity\ConstructionManager;
use App\Service\AuthorizationService;
use App\Service\Interfaces\AuthorizationServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class AuthorizationServiceTest extends WebTestCase
{
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
    }

    /**
     * @param string $authorizationMethod
     * @return AuthorizationService
     */
    private function getAuthorizationService(string $authorizationMethod = "none")
    {
        self::bootKernel();
        /** @var LoggerInterface $logger */
        $logger = self::$container->get(LoggerInterface::class);

        $pathService = $this->createMock(PathServiceInterface::class);
        $pathService->expects($this->any())->method('getTransientFolderRoot')->willReturn(__DIR__ . DIRECTORY_SEPARATOR . "Authorization");
        /** @var PathServiceInterface $pathService */

        $parameterBag = new ParameterBag(['AUTHORIZATION_METHOD' => $authorizationMethod]);

        return new AuthorizationService($pathService, $logger, $parameterBag);
    }

    /**
     * @throws \Exception
     */
    public function testCheckIfAuthorized_withMethodNone_authorizesAll()
    {
        // arrange
        $service = $this->getAuthorizationService("none");

        // act & assert
        $this->assertAuthorized($service, "something@nonreal.ch");
    }

    /**
     * @throws \Exception
     */
    public function testCheckIfAuthorized_withMethodInvalid_throwsException()
    {
        // arrange
        $service = $this->getAuthorizationService("invalid");

        // act
        $this->expectException(\Exception::class);
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail("something@nonreal.ch");
        $service->checkIfAuthorized($constructionManager);
    }

    /**
     * @throws \Exception
     */
    public function testCheckIfAuthorized_withMethodWhitelist_authorizesWhitelistOnly()
    {
        // arrange
        $service = $this->getAuthorizationService("whitelist");

        // act & assert
        $this->assertAuthorized($service, "info@mangel.io");
        $this->assertAuthorized($service, "info2@mangel.io");
        $this->assertAuthorized($service, "info3@mangel.io");
        $this->assertNotAuthorized($service, "info4@mangel.io");

        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail("invalid@invalid.com");

        // check trial accounts
        $constructionManager->setIsTrialAccount(true);
        $this->assertTrue($service->checkIfAuthorized($constructionManager));
        $constructionManager->setIsTrialAccount(false);

        // check external accounts
        $constructionManager->setIsExternalAccount(true);
        $this->assertTrue($service->checkIfAuthorized($constructionManager));
        $constructionManager->setIsExternalAccount(false);
    }

    /**
     * @throws \Exception
     */
    public function testTryFillDefaultValues_fillsValues()
    {
        // arrange
        $service = $this->getAuthorizationService();
        $infoConstructionManager = new ConstructionManager();
        $infoConstructionManager->setEmail("info@mangel.io");
        $info2ConstructionManager = new ConstructionManager();
        $info2ConstructionManager->setEmail("info2@mangel.io");
        $unknownConstructionManager = new ConstructionManager();
        $unknownConstructionManager->setEmail("unknown");

        // act
        $service->tryFillDefaultValues($infoConstructionManager);
        $service->tryFillDefaultValues($info2ConstructionManager);
        $service->tryFillDefaultValues($unknownConstructionManager);

        // assert
        $this->assertConstructionManager($infoConstructionManager, "info", "mangel.io", "42");
        $this->assertConstructionManager($info2ConstructionManager, "info2");
        $this->assertConstructionManager($unknownConstructionManager);
    }

    private function assertAuthorized(AuthorizationServiceInterface $authorizationService, string $email)
    {
        return $this->assertAuthorization($authorizationService, $email, true);
    }

    private function assertNotAuthorized(AuthorizationServiceInterface $authorizationService, string $email)
    {
        return $this->assertAuthorization($authorizationService, $email, false);
    }

    private function assertAuthorization(AuthorizationServiceInterface $authorizationService, string $email, bool $expected)
    {
        $constructionManager = new ConstructionManager();
        $constructionManager->setEmail($email);
        $isAuthorized = $authorizationService->checkIfAuthorized($constructionManager);

        if ($expected) {
            $this->assertTrue($isAuthorized);
        } else {
            $this->assertFalse($isAuthorized);
        }
    }

    /**
     * @param ConstructionManager $constructionManager
     * @param string|null $givenName
     * @param string|null $familyName
     * @param string|null $phone
     */
    private function assertConstructionManager(ConstructionManager $constructionManager, ?string $givenName = null, ?string $familyName = null, ?string $phone = null)
    {
        $this->assertEquals($givenName, $constructionManager->getGivenName());
        $this->assertEquals($familyName, $constructionManager->getFamilyName());
        $this->assertEquals($phone, $constructionManager->getPhone());
    }
}
