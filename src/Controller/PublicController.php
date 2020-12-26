<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseController;
use App\Entity\Craftsman;
use App\Entity\Filter;
use Http\Discovery\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicController extends BaseController
{
    /**
     * @Route("/resolve/{token}", name="public_resolve")
     *
     * @return Response
     */
    public function resolveAction(string $token)
    {
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['authenticationToken' => $token, 'deletedAt' => null]);
        if (null === $craftsman) {
            throw new NotFoundException();
        }

        return $this->render('public/resolve.html.twig');
    }

    /**
     * @Route("/filtered/{token}", name="public_filtered")
     *
     * @return Response
     */
    public function filteredAction(string $token)
    {
        $filter = $this->getDoctrine()->getRepository(Filter::class)->findOneBy(['authenticationToken' => $token]);
        if (null === $filter || $filter->getAccessAllowedBefore() < new \DateTime()) {
            throw new NotFoundException();
        }

        return $this->render('public/filtered.html.twig');
    }
}
