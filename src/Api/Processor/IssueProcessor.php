<?php

namespace App\Api\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Issue;
use App\Entity\IssueEvent;
use App\Helper\DoctrineHelper;
use App\Security\TokenTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class IssueProcessor implements ProcessorInterface
{
    use TokenTrait;

    /**
     * @param ProcessorInterface<Issue, Issue> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $persistProcessor,
        private ManagerRegistry $doctrine,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * @param Issue $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Issue
    {
        $repository = $this->doctrine->getRepository(Issue::class);
        if ($operation instanceof Post) {
            $data->setNumber(0);

            $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

            $repository->assignHighestNumber($result);

            $authority = $this->tryGetAuthority($this->tokenStorage->getToken());
            $issueEvents = IssueEvent::createFromChangedIssue(null, $result, $authority);
            DoctrineHelper::persistAndFlush($this->doctrine, ...$issueEvents);

            return $result;
        }

        if ($operation instanceof Patch) {
            /** @var EntityManagerInterface $objectManager */
            $objectManager = $this->doctrine->getManager();
            $unitOfWork = $objectManager->getUnitOfWork();
            $previousState = $unitOfWork->getOriginalEntityData($data);

            $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

            $authority = $this->tryGetAuthority($this->tokenStorage->getToken());
            $issueEvents = IssueEvent::createFromChangedIssue($previousState, $result, $authority);
            DoctrineHelper::persistAndFlush($this->doctrine, ...$issueEvents);

            return $result;
        }

        if ($operation instanceof Delete) {
            $data->markAsDeleted();

            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        throw new \Exception('Unsupported operation');
    }
}
