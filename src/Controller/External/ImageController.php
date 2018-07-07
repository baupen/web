<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External;

use App\Controller\Base\BaseDoctrineController;
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
 * @Route("/image")
 */
class ImageController extends BaseDoctrineController
{
    /**
     * @Route("/map/{map}/c/{identifier}/{hash}/{size}", name="external_image_map_craftsman")
     *
     * @param Map $map
     * @param $identifier
     * @param $size
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function mapAction(Map $map, $identifier, $size, ImageServiceInterface $imageService)
    {
        /** @var Craftsman $craftsman */
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['emailIdentifier' => $identifier]);
        if ($craftsman === null || $map->getConstructionSite() !== $craftsman->getConstructionSite()) {
            throw new NotFoundHttpException();
        }

        $filter = new Filter();
        $filter->setConstructionSite($craftsman->getConstructionSite()->getId());
        $filter->setCraftsmen([$craftsman->getId()]);
        $filter->setMaps([$map->getId()]);
        $filter->setRespondedStatus(false);
        $filter->setRegistrationStatus(true);
        $filter->setReviewedStatus(false);
        $issues = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);

        $imagePath = $imageService->generateMapImage($map, $issues);

        return $this->file($imageService->getSize($imagePath, $size), null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/issue/{issue}/c/{identifier}/{size}", name="external_image_map_craftsman")
     *
     * @param Issue $issue
     * @param $identifier
     * @param $size
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function issueAction(Issue $issue, $identifier, $size, ImageServiceInterface $imageService)
    {
        /** @var Craftsman $craftsman */
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['emailIdentifier' => $identifier]);
        if ($craftsman === null || $issue->getMap()->getConstructionSite() !== $craftsman->getConstructionSite()) {
            throw new NotFoundHttpException();
        }

        return $this->file($imageService->getSize($issue->getImageFilePath(), $size), null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
