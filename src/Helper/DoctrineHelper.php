<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
