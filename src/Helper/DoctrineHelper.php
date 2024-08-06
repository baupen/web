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
use Doctrine\Persistence\ManagerRegistry;

class DoctrineHelper
{
    /**
     * @return void
     */
    public static function persistAndFlush(ManagerRegistry $registry, BaseEntity ...$entities)
    {
        $manager = $registry->getManager();
        foreach ($entities as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
