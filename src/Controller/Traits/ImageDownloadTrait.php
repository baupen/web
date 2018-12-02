<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Traits;

use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ImageDownloadTrait
{
    /**
     * @param Issue $issue
     * @param string $expectedId
     * @param string $size
     * @param ImageServiceInterface $imageService
     *
     * @return string
     */
    protected function getImagePathForIssue(Issue $issue, $expectedId, $size, ImageServiceInterface $imageService)
    {
        if ($issue->getImage() === null || $issue->getImage()->getId() !== $expectedId) {
            throw new NotFoundHttpException();
        }

        $filePath = $imageService->getSizeForIssue($issue, $imageService->ensureValidSize($size));
        if ($filePath === null) {
            throw new NotFoundHttpException();
        }

        return $filePath;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param string $expectedId
     * @param string $size
     * @param ImageServiceInterface $imageService
     *
     * @return string
     */
    protected function getImagePathForConstructionSite(ConstructionSite $constructionSite, $expectedId, $size, ImageServiceInterface $imageService)
    {
        if ($constructionSite->getImage() === null || $constructionSite->getImage()->getId() !== $expectedId) {
            throw new NotFoundHttpException();
        }

        $filePath = $imageService->getSizeForConstructionSite($constructionSite, $imageService->ensureValidSize($size));
        if ($filePath === null) {
            throw new NotFoundHttpException();
        }

        return $filePath;
    }
}
