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
use App\Entity\Craftsman;
use App\Entity\IssueEvent;
use App\Entity\Map;
use App\Entity\Traits\SoftDeleteTrait;

class SoftDeleteDataPersister implements ContextAwareDataPersisterInterface
{
    private ContextAwareDataPersisterInterface $decorated;

    public function __construct(ContextAwareDataPersisterInterface $decoratedDataPersister)
    {
        $this->decorated = $decoratedDataPersister;
    }

    public function supports($data, array $context = []): bool
    {
        return ($data instanceof Craftsman || $data instanceof Map || $data instanceof IssueEvent)
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
