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
use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 */
class ImageController extends BaseDoctrineController
{
    /**
     * @Route("/issue/{issue}/{size}", name="image_issue")
     *
     * @param Issue $issue
     * @param $size
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function issueAction(Issue $issue, $size, ImageServiceInterface $imageService)
    {
        $filePath = $imageService->getSize($this->getParameter('PUBLIC_DIR') . '/' . $issue->getImageFilePath(), $size);
        if ($filePath === null) {
            throw new NotFoundHttpException();
        }

        return $this->file($filePath, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
