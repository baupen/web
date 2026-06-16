<?php

namespace App\Api\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ConstructionSite;
use App\Helper\DoctrineHelper;
use App\Security\TokenTrait;
use App\Service\Interfaces\SampleServiceInterface;
use App\Service\Interfaces\StorageServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

readonly class ConstructionSiteProcessor implements ProcessorInterface
{
    use TokenTrait;

    /**
     * @param ProcessorInterface<ConstructionSite, ConstructionSite> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $persistProcessor,
        private StorageServiceInterface $storageService,
        private SampleServiceInterface $sampleService,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * @param ConstructionSite $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ConstructionSite
    {
        /** @var HttpOperation $operation */
        if ($operation instanceof Post && $operation->getUriTemplate() === '/construction_sites/sample') {
            $constructionManager = $this->tryGetConstructionManager($this->tokenStorage->getToken());
            $name = $data->getName();
            $data = $this->sampleService->createSampleConstructionSite(SampleServiceInterface::SAMPLE_SIMPLE, $constructionManager);
            $data->setName($name);
            $data->setIsHidden(true);
        } elseif ($operation instanceof Post) {
            $this->storageService->setNewFolderName($data);
        }

        if ($operation instanceof Delete) {
            $data->markAsDeleted();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
