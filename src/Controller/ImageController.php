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
use App\Controller\Traits\ImageDownloadTrait;
use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 */
class ImageController extends BaseDoctrineController
{
    use ImageDownloadTrait;

    /**
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + [ImageServiceInterface::class => ImageServiceInterface::class];
    }

    /**
     * @Route("/issue/{issue}/{imageFilename}/{size}", name="image_issue")
     *
     * @param Issue $issue
     * @param string $imageFilename
     * @param string $size
     * @param ImageServiceInterface $imageService
     * @param PathServiceInterface $pathService
     *
     * @return Response
     */
    public function issueAction(Issue $issue, $imageFilename, $size, ImageServiceInterface $imageService, PathServiceInterface $pathService)
    {
        $this->ensureAccess($issue);

        return $this->file($this->getImagePath($issue, $imageFilename, $size, $pathService, $imageService), $imageFilename, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
