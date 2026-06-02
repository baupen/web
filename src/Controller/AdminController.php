<?php

namespace App\Controller;

use App\Entity\ConstructionManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/admin')]
class AdminController extends AbstractController
{
    #[Route(path: '', name: 'admin_index')]
    public function index(ManagerRegistry $registry): Response
    {
        $constructionManagerRepo = $registry->getRepository(ConstructionManager::class);
        $constructionManagers = $constructionManagerRepo->findBy([], ['email' => 'ASC']);

        return $this->render('admin.html.twig', ['constructionManagers' => $constructionManagers]);
    }
}
