<?php

namespace App\Controller;

use App\Controller\Traits\FileResponseTrait;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Helper\DoctrineHelper;
use App\Security\TokenTrait;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PublicController extends AbstractController
{
    use TokenTrait;
    use FileResponseTrait;

    #[Route(path: '/download/{filename}', name: 'public_download')]
    public function download(string $filename, PathServiceInterface $pathService): Response
    {
        $path = $pathService->getTransientFolderForReports();

        $response = $this->tryCreateAttachmentFileResponse($path . '/' . $filename, $filename);
        $response->deleteFileAfterSend();

        return $response;
    }

    #[Route(path: '/resolve/{token}', name: 'public_resolve')]
    public function resolve(string $token, TokenStorageInterface $tokenStorage, ManagerRegistry $registry): Response
    {
        $craftsman = $registry->getRepository(Craftsman::class)->findOneBy(['authenticationToken' => $token, 'deletedAt' => null]);
        if (null === $craftsman) {
            throw $this->createNotFoundException();
        }

        if (!$this->tryGetConstructionManager($tokenStorage->getToken())) {
            $craftsman->setLastVisitOnline(new \DateTimeImmutable());
            DoctrineHelper::persistAndFlush($registry, $craftsman);
        }

        return $this->render('public/resolve.html.twig');
    }

    #[Route(path: '/filtered/{token}', name: 'public_filtered')]
    public function filtered(string $token, TokenStorageInterface $tokenStorage, ManagerRegistry $registry): Response
    {
        $filter = $registry->getRepository(Filter::class)->findOneBy(['authenticationToken' => $token]);
        if (null === $filter || ($filter->getAccessAllowedBefore() && $filter->getAccessAllowedBefore() < new \DateTimeImmutable())) {
            throw $this->createNotFoundException();
        }

        if (!$this->tryGetConstructionManager($tokenStorage->getToken())) {
            $filter->setLastUsedAt();
            DoctrineHelper::persistAndFlush($registry, $filter);
        }

        return $this->render('public/filtered.html.twig');
    }
}
