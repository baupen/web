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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 */
class ImageController extends BaseDoctrineController
{
    /**
     * @Route("/map/{map}/c/{identifier}/", name="external_image_map_craftsman")
     *
     * @param Map $map
     * @param $identifier
     *
     * @return Response
     */
    public function imageAction(Map $map, $identifier)
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

        return $this->file($this->getOrCreateIssuesImage($map, $issues));
    }

    private function getOrCreateIssuesImage(Map $map, array $issues)
    {
        $folder = __DIR__ . '/../../../public/generated/' . $map->getConstructionSite()->getId() . '/map/' . $map->getId();
        $filename = hash('sha256', implode(',', array_map(function ($issue) {
            /* @var Issue $issue */
            return $issue->getId();
        }, $issues)));
        $filePath = $folder . '/' . $filename;

        if (file_exists($filePath)) {
            return $filePath;
        }

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        return null;
    }
}
