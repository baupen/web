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
use App\Security\TokenTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api")
 */
class ApiController extends BaseController
{
    use TokenTrait;

    /**
     * @Route("/me", name="api_me")
     *
     * @return Response
     */
    public function meAction(TokenStorageInterface $tokenStorage)
    {
        $data = [];
        $token = $tokenStorage->getToken();

        $constructionManager = $this->tryGetConstructionManager($token);
        if (null !== $constructionManager) {
            $data['constructionManagerId'] = $constructionManager->getId();
        }

        $craftsman = $this->tryGetCraftsman($token);
        if (null !== $craftsman) {
            $data['craftsmanId'] = $craftsman->getId();
        }

        $filter = $this->tryGetFilter($token);
        if (null !== $filter) {
            $data['filterId'] = $filter->getId();
        }

        return $this->json($data);
    }
}
