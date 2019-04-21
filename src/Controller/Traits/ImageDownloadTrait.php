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
use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ImageDownloadTrait
{
    /**
     * @param Issue                 $issue
     * @param IssueImage            $image
     * @param string                $size
     * @param ImageServiceInterface $imageService
     *
     * @return string
     */
    protected function getImagePathForIssue(Issue $issue, IssueImage $image, $size, ImageServiceInterface $imageService)
    {
        if ($issue->getImage() !== $image) {
            throw new NotFoundHttpException();
        }

        $filePath = $imageService->getSizeForIssue($issue, $imageService->ensureValidSize($size));
        if ($filePath === null) {
            throw new NotFoundHttpException();
        }

        return $filePath;
    }

    /**
     * @param ConstructionSite      $constructionSite
     * @param ConstructionSiteImage $image
     * @param string                $size
     * @param ImageServiceInterface $imageService
     *
     * @return string
     */
    protected function getImagePathForConstructionSite(ConstructionSite $constructionSite, ConstructionSiteImage $image, $size, ImageServiceInterface $imageService)
    {
        if ($constructionSite->getImage() !== $image) {
            throw new NotFoundHttpException();
        }

        $filePath = $imageService->getSizeForConstructionSite($constructionSite, $imageService->ensureValidSize($size));
        if ($filePath === null) {
            throw new NotFoundHttpException();
        }

        return $filePath;
    }
}
