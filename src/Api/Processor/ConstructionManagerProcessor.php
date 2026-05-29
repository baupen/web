<?php

namespace App\Api\Processor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Security\TokenTrait;
use App\Service\Interfaces\StorageServiceInterface;
use App\Service\Interfaces\UserServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class ConstructionManagerProcessor implements ProcessorInterface
{
    use TokenTrait;

    /**
     * @param ProcessorInterface<ConstructionManager, ConstructionManager> $persistProcessor
     * @param ProcessorInterface<ConstructionManager, ConstructionManager> $removeProcessor
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private UserServiceInterface $userService,
        private ManagerRegistry $managerRegistry,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * @param ConstructionManager $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ConstructionManager
    {
        if ($operation instanceof Post) {
            $registrationSuccessful = $this->userService->tryRegister($data, $error);

            if (!$registrationSuccessful) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, $error);
            }
        }

        if ($operation instanceof Delete) {
            $data->delete();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
