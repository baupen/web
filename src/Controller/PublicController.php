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

use App\Controller\Base\BaseDoctrineController;
use App\Controller\Traits\FileResponseTrait;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Security\TokenTrait;
use App\Service\Interfaces\PathServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PublicController extends BaseDoctrineController
{
    use TokenTrait;
    use FileResponseTrait;

    /**
     * @Route("/download/{filename}", name="public_download")
     *
     * @return Response
     */
    public function downloadAction(string $filename, PathServiceInterface $pathService)
    {
        $path = $pathService->getTransientFolderForReports();

        return $this->tryCreateAttachmentFileResponse($path.'/'.$filename, $filename, true);
    }

    /**
     * @Route("/resolve/{token}", name="public_resolve")
     *
     * @return Response
     */
    public function resolveAction(string $token, TokenStorageInterface $tokenStorage)
    {
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['authenticationToken' => $token, 'deletedAt' => null]);
        if (null === $craftsman) {
            throw new NotFoundHttpException();
        }

        if (!$this->tryGetConstructionManager($tokenStorage->getToken())) {
            $craftsman->setLastVisitOnline(new \DateTime());
            $this->fastSave($craftsman);
        }

        return $this->render('public/resolve.html.twig');
    }

    /**
     * @Route("/filtered/{token}", name="public_filtered")
     *
     * @return Response
     */
    public function filteredAction(string $token, TokenStorageInterface $tokenStorage)
    {
        $filter = $this->getDoctrine()->getRepository(Filter::class)->findOneBy(['authenticationToken' => $token]);
        if (null === $filter || $filter->getAccessAllowedBefore() < new \DateTime()) {
            throw new NotFoundHttpException();
        }

        if (!$this->tryGetConstructionManager($tokenStorage->getToken())) {
            $filter->setLastUsedAt();
            $this->fastSave($filter);
        }

        return $this->render('public/filtered.html.twig');
    }
}
