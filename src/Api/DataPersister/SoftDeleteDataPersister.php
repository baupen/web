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
use App\Entity\Craftsman;
use App\Entity\Map;
use App\Entity\Traits\SoftDeleteTrait;
use App\Service\Interfaces\StorageServiceInterface;
use Symfony\Component\Security\Core\Security;

class SoftDeleteDataPersister implements ContextAwareDataPersisterInterface
{
    private $decorated;
    private $storageService;
    private $user;

    public function __construct(ContextAwareDataPersisterInterface $decoratedDataPersister, StorageServiceInterface $storageService, Security $security)
    {
        $this->decorated = $decoratedDataPersister;
        $this->storageService = $storageService;
        $this->user = $security->getUser();
    }

    public function supports($data, array $context = []): bool
    {
        return ($data instanceof Craftsman || $data instanceof Map)
            && $this->decorated->supports($data, $context);
    }

    /**
     * @param SoftDeleteTrait $data
     */
    public function persist($data, array $context = [])
    {
        return $this->decorated->persist($data, $context);
    }

    /**
     * @param SoftDeleteTrait $data
     */
    public function remove($data, array $context = [])
    {
        $data->delete();

        return $this->decorated->persist($data, $context);
    }
}
