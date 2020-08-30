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
use Symfony\Component\HttpFoundation\Request;

trait CraftsmanAuthenticationTrait
{
    /**
     * @param string $identifier
     * @param $craftsman
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

    /**
     * @return bool
     */
    private function checkWriteAuthenticationToken(Request $request, Craftsman $craftsman)
    {
        $token = $request->query->get('token');

        return $craftsman->getWriteAuthorizationToken() === $token;
    }

    /**
     * @return Filter
     */
    private static function createCraftsmanFilter(Craftsman $craftsman)
    {
        $filter = new Filter();

        $filter->setConstructionSite($craftsman->getConstructionSite());
        $filter->filterByCraftsmen([$craftsman->getId()]);
        $filter->filterByRespondedStatus(false);
        $filter->filterByRegistrationStatus(true);
        $filter->filterByReviewedStatus(false);

        return $filter;
    }
}
