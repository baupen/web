<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Controller\Api\Base\AbstractApiController;
use App\Entity\ConstructionSite;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/craftsman")
 */
class CraftsmanController extends AbstractApiController
{
    /**
     * @Route("/{constructionSite}/list", name="api_craftsman_list")
     *
     * @param ConstructionSite $constructionSite
     *
     * @return Response
     */
    public function listAction(ConstructionSite $constructionSite)
    {
        $craftsmen = $constructionSite->getCraftsmen();

        return $this->json($this->get('serializer')->serialize($craftsmen, 'json', ['attributes' => ['name']]));
    }
}
