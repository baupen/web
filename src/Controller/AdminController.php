<?php

namespace App\Controller;

use App\Controller\Base\BaseController;
use App\Entity\ConstructionManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin')]
class AdminController extends BaseController
{
    #[Route(path: '', name: 'admin_index')]
    public function index(ManagerRegistry $registry): Response
    {
        $constructionManagerRepo = $registry->getRepository(ConstructionManager::class);
        $constructionManagers = $constructionManagerRepo->findBy([], ['email' => 'ASC']);

        return $this->render('admin.html.twig', ['constructionManagers' => $constructionManagers]);
    }
}
