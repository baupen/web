<?php

namespace App\Api\Processor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ConstructionSite;
use App\Service\Interfaces\StorageServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ConstructionSiteProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<ConstructionSite, ConstructionSite> $persistProcessor
     * @param ProcessorInterface<ConstructionSite, ConstructionSite> $removeProcessor
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private StorageServiceInterface $storageService,
    ) {
    }

    /**
     * @param ConstructionSite $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ConstructionSite
    {
        if ($operation instanceof Post) {
            $this->storageService->setNewFolderName($data);
        }

        if ($operation instanceof Delete) {
            $data->markAsDeleted();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
