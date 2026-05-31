<?php

namespace App\Api\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Map;
use App\Security\TokenTrait;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class SoftDeleteProcessor implements ProcessorInterface
{
    use TokenTrait;

    /**
     * @param ProcessorInterface<Craftsman|Map, Craftsman|Map> $persistProcessor
     * @param ProcessorInterface<Craftsman|Map, Craftsman|Map> $removeProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $persistProcessor,
        #[Autowire(service: RemoveProcessor::class)] private ProcessorInterface $removeProcessor,
        private ManagerRegistry $doctrine,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * @param Issue $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Craftsman|Map
    {
        if ($operation instanceof Delete) {
            $data->markAsDeleted();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
