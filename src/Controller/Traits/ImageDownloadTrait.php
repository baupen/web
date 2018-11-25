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

use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ImageDownloadTrait
{
    /**
     * @param Issue $issue
     * @param $expectedImageFilename
     * @param $size
     * @param PathServiceInterface $pathService
     * @param ImageServiceInterface $imageService
     *
     * @return string
     */
    protected function getImagePath(Issue $issue, $expectedImageFilename, $size, PathServiceInterface $pathService, ImageServiceInterface $imageService)
    {
        if ($issue->getImageFilename() !== $expectedImageFilename) {
            throw new NotFoundHttpException();
        }

        $filePath = $imageService->getSize($pathService->getFolderForIssue($issue) . \DIRECTORY_SEPARATOR . $issue->getImageFilename(), $size);
        if ($filePath === null) {
            throw new NotFoundHttpException();
        }

        return $filePath;
    }
}
