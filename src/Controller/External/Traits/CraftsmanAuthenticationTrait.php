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
use Doctrine\Common\Persistence\ManagerRegistry;

trait CraftsmanAuthenticationTrait
{
    /**
     * @param ManagerRegistry $doctrine
     * @param string $identifier
     * @param $craftsman
     * @param $errorResponse
     *
     * @return bool
     */
    private function parseIdentifierRequest(ManagerRegistry $doctrine, $identifier, &$craftsman)
    {
        /** @var Craftsman $craftsman */
        $craftsman = $doctrine->getRepository(Craftsman::class)->findOneBy(['emailIdentifier' => $identifier]);
        if ($craftsman === null) {
            return false;
        }

        return true;
    }
}
