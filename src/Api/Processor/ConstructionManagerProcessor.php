<?php

namespace App\Api\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ConstructionManager;
use App\Security\TokenTrait;
use App\Service\Interfaces\UserServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly class ConstructionManagerProcessor implements ProcessorInterface
{
    use TokenTrait;

    /**
     * @param ProcessorInterface<ConstructionManager, ConstructionManager> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $persistProcessor,
        private UserServiceInterface $userService,
    ) {
    }

    /**
     * @param ConstructionManager $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ConstructionManager
    {
        if ($operation instanceof Post) {
            // we do not process errors here; the user registers here third-party construction managers, hence errors somewhat irrelevant at this point
            $this->userService->tryRegister($data, $error);
            return $data;
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
