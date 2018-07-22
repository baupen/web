<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External\Image;

use App\Controller\Base\BaseDoctrineController;
use App\Controller\External\Traits\CraftsmanAuthenticationTrait;
use App\Controller\Traits\ImageDownloadTrait;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/c/{identifier}")
 */
class CraftsmanController extends BaseDoctrineController
{
    use ImageDownloadTrait;
    use CraftsmanAuthenticationTrait;

    /**
     * @Route("/map/{map}/{hash}/{size}", name="external_image_craftsman_map")
     *
     * @param $identifier
     * @param Map $map
     * @param $size
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function mapAction($identifier, Map $map, $size, ImageServiceInterface $imageService)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $craftsman, $errorResponse)) {
            throw new NotFoundHttpException();
        }

        //get issues to put on map
        $filter = new Filter();
        $filter->setConstructionSite($craftsman->getConstructionSite()->getId());
        $filter->setCraftsmen([$craftsman->getId()]);
        $filter->setMaps([$map->getId()]);
        $filter->setRespondedStatus(false);
        $filter->setRegistrationStatus(true);
        $filter->setReviewedStatus(false);
        $issues = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);

        //generate map & print
        $imagePath = $imageService->generateMapImage($map, $issues);

        return $this->file($imageService->getSize($imagePath, $size), null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/issue/{issue}/{imageFilename}/{size}", name="external_image_craftsman_issue")
     *
     * @param $identifier
     * @param Issue $issue
     * @param $imageFilename
     * @param $size
     *
     * @return Response
     */
    public function issueAction($identifier, Issue $issue, $imageFilename, $size)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $craftsman, $errorResponse)) {
            throw new NotFoundHttpException();
        }

        return $this->downloadIssueImage($issue, $imageFilename, $size);
    }
}
