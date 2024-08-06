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
use App\Controller\Traits\FileResponseTrait;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Helper\DoctrineHelper;
use App\Security\TokenTrait;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PublicController extends BaseController
{
    use TokenTrait;
    use FileResponseTrait;

    /**
     * @return Response
     */
    #[Route(path: '/download/{filename}', name: 'public_download')]
    public function download(string $filename, PathServiceInterface $pathService)
    {
        $path = $pathService->getTransientFolderForReports();

        $response = $this->tryCreateAttachmentFileResponse($path.'/'.$filename, $filename);
        $response->deleteFileAfterSend();

        return $response;
    }

    #[Route(path: '/resolve/{token}', name: 'public_resolve')]
    public function resolve(string $token, TokenStorageInterface $tokenStorage, ManagerRegistry $registry): Response
    {
        $craftsman = $registry->getRepository(Craftsman::class)->findOneBy(['authenticationToken' => $token, 'deletedAt' => null]);
        if (null === $craftsman) {
            throw new NotFoundHttpException();
        }

        if (!$this->tryGetConstructionManager($tokenStorage->getToken()) instanceof \App\Entity\ConstructionManager) {
            $craftsman->setLastVisitOnline(new \DateTime());
            DoctrineHelper::persistAndFlush($registry, $craftsman);
        }

        return $this->render('public/resolve.html.twig');
    }

    #[Route(path: '/filtered/{token}', name: 'public_filtered')]
    public function filtered(string $token, TokenStorageInterface $tokenStorage, ManagerRegistry $registry): Response
    {
        $filter = $registry->getRepository(Filter::class)->findOneBy(['authenticationToken' => $token]);
        if (null === $filter || ($filter->getAccessAllowedBefore() && $filter->getAccessAllowedBefore() < new \DateTime())) {
            throw new NotFoundHttpException();
        }

        if (!$this->tryGetConstructionManager($tokenStorage->getToken()) instanceof \App\Entity\ConstructionManager) {
            $filter->setLastUsedAt();
            DoctrineHelper::persistAndFlush($registry, $filter);
        }

        return $this->render('public/filtered.html.twig');
    }
}
