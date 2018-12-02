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
use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
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
     * @Route("/issue/{issue}/{imageId}/{size}", name="image_issue")
     *
     * @param Issue $issue
     * @param string $imageId
     * @param string $size
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function issueAction(Issue $issue, $imageId, $size, ImageServiceInterface $imageService)
    {
        $this->ensureAccess($issue);

        return $this->file($this->getImagePathForIssue($issue, $imageId, $size, $imageService), $issue->getImage()->getFilename(), ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/constructionSite/{constructionSite}/{imageId}/{size}", name="image_construction_site")
     *
     * @param ConstructionSite $constructionSite
     * @param string $imageId
     * @param string $size
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function constructionSiteAction(ConstructionSite $constructionSite, $imageId, $size, ImageServiceInterface $imageService)
    {
        $this->ensureAccess($constructionSite);

        return $this->file($this->getImagePathForConstructionSite($constructionSite, $imageId, $size, $imageService), $constructionSite->getImage()->getFilename(), ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
