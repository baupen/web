<?php

namespace App\Controller;

use App\Entity\Email;
use App\Helper\DoctrineHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/email')]
class EmailController extends AbstractController
{
    #[Route(path: '/{identifier}', name: 'email')]
    public function email(string $identifier, ManagerRegistry $registry): Response
    {
        $email = $registry->getRepository(Email::class)->findOneBy(['identifier' => $identifier]);
        if (null === $email) {
            throw $this->createNotFoundException()();
        }

        $email->markRead();
        DoctrineHelper::persistAndFlush($registry, $email);

        return $this->render('email/_view_online_base.html.twig', $email->getContext());
    }
}
