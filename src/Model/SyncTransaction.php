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
     * @var IdTrait[]
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
            $array = $this->getOrCreateArray($class, $this->newEntities);

            if (!\in_array($entity, $array, true)) {
                $array[] = $entity;
            }
        } else {
            $array = $this->getOrCreateArray($class, $this->editedEntities);
            if (!array_key_exists($identifier, $array)) {
                $array[$identifier] = $entity;
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
            $array = $this->getOrCreateArray($class, $this->removedEntities);
            if (!array_key_exists($identifier, $array)) {
                $array[$identifier] = $entity;
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

    private function getOrCreateArray(string $key, array &$array)
    {
        if (!array_key_exists($key, $array)) {
            $array[$key] = [];
        }

        return $array[$key];
    }
}
