<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External\Traits;

use App\Entity\Filter;
use Doctrine\Common\Persistence\ManagerRegistry;

trait FilterAuthenticationTrait
{
    /**
     * @param ManagerRegistry $doctrine
     * @param $identifier
     * @param $filter
     *
     * @throws \Exception
     *
     * @return bool
     */
    private function parseIdentifierRequest(ManagerRegistry $doctrine, $identifier, &$filter)
    {
        /** @var Filter $filter */
        $filter = $doctrine->getRepository(Filter::class)->findOneBy(['publicAccessIdentifier' => $identifier]);
        if ($filter === null) {
            return false;
        }

        return true;
    }
}
