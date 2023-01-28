<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseController;
use App\Entity\ConstructionManager;
use App\Service\UserService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends BaseController
{
    /**
     * @Route("", name="admin_index")
     *
     * @return Response
     */
    public function indexAction(ManagerRegistry $registry)
    {
        $constructionManagerRepo = $registry->getRepository(ConstructionManager::class);
        $constructionManagers = $constructionManagerRepo->findBy([], ['email' => 'ASC']);

        return $this->render('admin.html.twig', ['constructionManagers' => $constructionManagers]);
    }
}
