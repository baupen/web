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

use App\Entity\Craftsman;
use App\Entity\Filter;
use Doctrine\Common\Persistence\ManagerRegistry;

trait FilterAuthenticationTrait
{
    /**
     * @param ManagerRegistry $doctrine
     * @param $identifier
     * @param $filter
     * @param $errorResponse
     *
     * @return bool
     */
    private function parseIdentifierRequest(ManagerRegistry $doctrine, $identifier, &$filter, &$errorResponse)
    {
        /** @var Craftsman $filter */
        $filter = $doctrine->getRepository(Filter::class)->findOneBy(['accessIdentifier' => $identifier]);
        if ($filter === null) {
            $errorResponse = $this->fail(self::INVALID_IDENTIFIER);

            return false;
        }

        return true;
    }
}
