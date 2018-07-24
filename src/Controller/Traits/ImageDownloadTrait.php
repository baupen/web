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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ImageDownloadTrait
{
    /**
     * @param string $publicDir
     * @param Issue $issue
     * @param $imageFilename
     * @param $size
     * @param ImageServiceInterface $imageService
     *
     * @return string
     */
    protected function getImagePath($publicDir, Issue $issue, $imageFilename, $size, ImageServiceInterface $imageService)
    {
        if ($issue->getImageFilename() !== $imageFilename) {
            throw new NotFoundHttpException();
        }

        $filePath = $imageService->getSize($publicDir . '/' . $issue->getImageFilePath(), $size);
        if ($filePath === null) {
            throw new NotFoundHttpException();
        }

        return $filePath;
    }
}
