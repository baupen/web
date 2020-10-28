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
use App\Entity\ConstructionSite;
use App\Service\Interfaces\StorageServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

class StorageServiceDataPersister implements ContextAwareDataPersisterInterface
{
    private $decorated;
    private $storageService;
    private $doctrine;
    private $request;

    public function __construct(ContextAwareDataPersisterInterface $decoratedDataPersister, StorageServiceInterface $storageService, ManagerRegistry $registry, RequestStack $requestStack)
    {
        $this->decorated = $decoratedDataPersister;
        $this->storageService = $storageService;
        $this->doctrine = $registry;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof ConstructionSite &&
            ($context['collection_operation_name'] ?? null) === 'post' &&
            $this->decorated->supports($data, $context);
    }

    public function persist($data, array $context = [])
    {
        if (($context['collection_operation_name'] ?? null) === 'post') {
            $this->storageService->setNewFolderName($data);
        }

        /** @var ConstructionSite $result */
        $result = $this->decorated->persist($data, $context);

        // upload image
        if ($this->request->files->count() > 0) {
            $file = $this->request->files[0];
            $image = $this->storageService->uploadConstructionSiteImage($file, $result);

            $manager = $this->doctrine->getManager();
            $manager->persist($image);
            $manager->persist($result);
            $manager->flush();
        }

        return $result;
    }

    public function remove($data, array $context = [])
    {
        return $this->decorated->remove($data, $context);
    }
}
