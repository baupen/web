<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use App\Entity\Traits\IdTrait;
use Doctrine\Common\Persistence\ObjectManager;

class SyncTransaction
{
    /**
     * @var IdTrait[][]
     */
    private $editedEntities = [];

    /**
     * @var IdTrait[][]
     */
    private $newEntities = [];

    /**
     * @var IdTrait[][]
     */
    private $removedEntities = [];

    /**
     * @param IdTrait $entity
     */
    public function persist($entity)
    {
        $class = \get_class($entity);
        $identifier = $entity->getId();
        if ($identifier === null) {
            if (!\array_key_exists($class, $this->newEntities)) {
                $this->newEntities[$class] = [];
            }

            if (!\in_array($entity, $this->newEntities, true)) {
                $this->newEntities[$class][] = $entity;
            }
        } else {
            if (!\array_key_exists($class, $this->editedEntities)) {
                $this->editedEntities[$class] = [];
            }

            if (!\array_key_exists($identifier, $this->editedEntities[$class])) {
                $this->editedEntities[$class][$identifier] = $entity;
            }
        }
    }

    /**
     * @param IdTrait $entity
     */
    public function remove($entity)
    {
        $class = \get_class($entity);
        $identifier = $entity->getId();
        if ($identifier !== null) {
            if (!\array_key_exists($class, $this->removedEntities)) {
                $this->removedEntities[$class] = [];
            }

            if (!\array_key_exists($identifier, $this->removedEntities[$class])) {
                $this->removedEntities[$class][$identifier] = $entity;
            }
        }
    }

    /**
     * @param ObjectManager $manager
     * @param callable $canPersist taking an IdEntity as arg
     */
    public function execute(ObjectManager $manager, callable $canPersist)
    {
        foreach ($this->editedEntities as $class => $editedEntities) {
            foreach ($editedEntities as $editedEntity) {
                if ($canPersist($editedEntity, $class)) {
                    $manager->persist($editedEntity);
                }
            }
        }

        foreach ($this->newEntities as $class => $newEntities) {
            foreach ($newEntities as $newEntity) {
                if ($canPersist($newEntity, $class)) {
                    $manager->persist($newEntity);
                }
            }
        }

        foreach ($this->removedEntities as $removedEntities) {
            foreach ($removedEntities as $removedEntity) {
                $manager->remove($removedEntity);
            }
        }
    }
}
