<?php

namespace App\Helper;

use App\Entity\Base\BaseEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineHelper
{
    public static function persistAndFlush(ManagerRegistry $registry, BaseEntity ...$entities): void
    {
        $manager = $registry->getManager();
        foreach ($entities as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
    }

    /**
     * @param Collection<int, BaseEntity> $collection
     *
     * @return array<string>
     */
    public static function getIdList(Collection $collection): array
    {
        $ids = [];

        foreach ($collection->toArray() as $entry) {
            $ids[] = $entry->getId();
        }

        return $ids;
    }
}
