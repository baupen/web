<?php

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\ConstructionSite;
use App\Service\Interfaces\StorageServiceInterface;

class ConstructionSiteDataPersister implements ContextAwareDataPersisterInterface
{
    private ContextAwareDataPersisterInterface $decorated;
    private StorageServiceInterface $storageService;

    public function __construct(ContextAwareDataPersisterInterface $decoratedDataPersister, StorageServiceInterface $storageService)
    {
        $this->decorated = $decoratedDataPersister;
        $this->storageService = $storageService;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof ConstructionSite
            && $this->decorated->supports($data, $context);
    }

    /**
     * @param ConstructionSite $data
     */
    public function persist($data, array $context = [])
    {
        if (($context['collection_operation_name'] ?? null) === 'post') {
            $this->storageService->setNewFolderName($data);
        }

        return $this->decorated->persist($data, $context);
    }

    /**
     * @param ConstructionSite $data
     */
    public function remove($data, array $context = [])
    {
        $data->delete();

        return $this->decorated->persist($data, $context);
    }
}
