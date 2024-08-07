<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Issue;
use App\Entity\ProtocolEntry;
use App\Helper\DoctrineHelper;
use App\Security\TokenTrait;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class IssueDataPersister implements ContextAwareDataPersisterInterface
{
    use TokenTrait;

    private ContextAwareDataPersisterInterface $decorated;
    private ManagerRegistry $doctrine;
    private TokenStorageInterface $tokenStorage;

    public function __construct(ContextAwareDataPersisterInterface $decoratedDataPersister, ManagerRegistry $registry, TokenStorageInterface $tokenStorage)
    {
        $this->decorated = $decoratedDataPersister;
        $this->doctrine = $registry;
        $this->tokenStorage = $tokenStorage;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Issue
            && $this->decorated->supports($data, $context);
    }

    /**
     * @param Issue $data
     */
    public function persist($data, array $context = []): void
    {
        $isCreated = ($context['collection_operation_name'] ?? null) === 'post';
        $previousState = null;
        if ($isCreated) {
            $data->setNumber(0);
        } else {
            $previousState = $data;
            $data = clone $data;

            $entityManager = $this->doctrine->getManager();
            $entityManager->refresh($previousState);
            $entityManager->detach($previousState);
            $entityManager->persist($data);
        }

        $repository = $this->doctrine->getRepository(Issue::class);

        /** @var Issue $result */
        $result = $this->decorated->persist($data, $context);

        if ($isCreated) {
            $repository->assignHighestNumber($result);
        }

        $authority = $this->tryGetAuthority($this->tokenStorage->getToken());
        $protocolEntries = ProtocolEntry::createFromChangedIssue($previousState, $result, $authority);
        DoctrineHelper::persistAndFlush($this->doctrine, ...$protocolEntries);
    }

    /**
     * @param Issue $data
     */
    public function remove($data, array $context = [])
    {
        $data->delete();

        return $this->decorated->persist($data, $context);
    }
}
