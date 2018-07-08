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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ImageDownloadTrait
{
    /**
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + [ImageServiceInterface::class => ImageServiceInterface::class];
    }

    /**
     * @param Issue $issue
     * @param $imageFilename
     * @param $size
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function downloadIssueImage(Issue $issue, $imageFilename, $size)
    {
        if ($issue->getImageFilename() !== $imageFilename) {
            throw new NotFoundHttpException();
        }

        /** @var ImageServiceInterface $imageService */
        $imageService = $this->get(ImageServiceInterface::class);
        $filePath = $imageService->getSize($this->getParameter('PUBLIC_DIR') . '/' . $issue->getImageFilePath(), $size);
        if ($filePath === null) {
            throw new NotFoundHttpException();
        }

        return $this->file($filePath, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
