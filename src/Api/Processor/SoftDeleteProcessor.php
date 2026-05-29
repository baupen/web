<?php

namespace App\Api\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\IssueEvent;
use App\Enum\IssueState;
use App\Helper\DoctrineHelper;
use App\Security\TokenTrait;
use App\Service\Interfaces\StorageServiceInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class SoftDeleteProcessor implements ProcessorInterface
{
    use TokenTrait;

    /**
     * @param ProcessorInterface<Issue, Issue> $persistProcessor
     * @param ProcessorInterface<Issue, Issue> $removeProcessor
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
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Issue
    {
        if ($operation instanceof Delete) {
            $data->markAsDeleted();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
