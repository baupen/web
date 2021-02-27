<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Issue;
use App\Service\Interfaces\StorageServiceInterface;
use Doctrine\Persistence\ManagerRegistry;

class IssueDataPersister implements ContextAwareDataPersisterInterface
{
    private $decorated;
    private $storageService;
    private $doctrine;

    public function __construct(ContextAwareDataPersisterInterface $decoratedDataPersister, StorageServiceInterface $storageService, ManagerRegistry $registry)
    {
        $this->decorated = $decoratedDataPersister;
        $this->storageService = $storageService;
        $this->doctrine = $registry;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Issue &&
            $this->decorated->supports($data, $context);
    }

    /**
     * @param Issue $data
     */
    public function persist($data, array $context = [])
    {
        $isCreated = ($context['collection_operation_name'] ?? null) === 'post';
        if ($isCreated) {
            $data->setNumber(0);
        }

        /** @var Issue $result */
        $result = $this->decorated->persist($data, $context);

        if ($isCreated) {
            $repository = $this->doctrine->getRepository(Issue::class);
            $repository->assignHighestNumber($result);
        }
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
