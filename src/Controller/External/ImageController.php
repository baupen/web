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
use App\Helper\HashHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 */
class ImageController extends BaseDoctrineController
{
    /**
     * @Route("/map/{map}/c/{identifier}/{hash}", name="external_image_map_craftsman")
     *
     * @param Map $map
     * @param $identifier
     * @param $hash
     *
     * @return Response
     */
    public function imageAction(Map $map, $identifier, $hash)
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

        $fileHash = HashHelper::hashEntities($issues);
        if ($fileHash !== $hash) {
            throw new NotFoundHttpException();
        }

        return $this->file($this->getOrCreateIssuesImage($map, $issues, $fileHash));
    }

    /**
     * @param Map $map
     * @param Issue[] $issues
     * @param $filename
     *
     * @return string
     */
    private function getOrCreateIssuesImage(Map $map, array $issues, $filename)
    {
        $pubFolder = __DIR__ . '/../../../public';

        //construct filename & directly return if already exists
        $folder = $pubFolder . '/generated/' . $map->getConstructionSite()->getId() . '/map/' . $map->getId();
        $filename = $filename . '.jpg';
        $filePath = $folder . '/' . $filename;

        if (file_exists($filePath) && false) {
            return $filePath;
        }

        //compile map
        $renderedMapPath = $folder . '/render.jpg';
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        if (!file_exists($renderedMapPath)) {
            $mapFilePath = $pubFolder . '/' . $map->getFilePath();
            $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=1920 -dDEVICEHEIGHTPOINTS=1080 -dJPEGQ=80 -dUseCropBox -dPDFFitPage -sPageList=1 -o ' . $renderedMapPath . ' ' . $mapFilePath;
            exec($command);
        }

        //draw the issues on the map
        $sourceImage = imagecreatefromjpeg($renderedMapPath);
        foreach ($issues as $issue) {
            $this->draw($issue, $sourceImage);
        }
        imagejpeg($sourceImage, $filePath, 90);
        imagedestroy($sourceImage);

        return $filePath;
    }

    /**
     * @param Issue $issue
     * @param $image
     */
    private function draw(Issue $issue, &$image)
    {
        $xSize = imagesx($image);
        $ySize = imagesy($image);

        //target location
        $yCoordinate = $issue->getPositionY() * $ySize;
        $xCoordinate = $issue->getPositionX() * $xSize;

        //white font; orange circle
        //colors sometime do not work and show up as black. just choose another color as close as possible to workaround
        $white = $this->createColor($image, 255, 255, 255);
        $orange = $this->createColor($image, 255, 204, 51);

        //get text size
        $font = __DIR__ . '/../../../assets/fonts/OpenSans-Regular.ttf';
        $fontSize = 20;
        $txtSize = imagettfbbox($fontSize, 0, $font, (string)$issue->getNumber());
        $txtWidth = abs($txtSize[4] - $txtSize[0]);
        $txtHeight = abs($txtSize[5] - $txtSize[1]);

        //calculate diameter around text
        $buffer = 20;
        $diameter = max($txtWidth, $txtHeight) + $buffer;

        //draw ellipses
        imagefilledellipse($image, $xCoordinate, $yCoordinate, $diameter + 2, $diameter + 2, $white);
        imagefilledellipse($image, $xCoordinate, $yCoordinate, $diameter, $diameter, $orange);

        //draw text
        imagettftext($image, $fontSize, 0, $xCoordinate - ($txtWidth / 2), $yCoordinate + ($txtHeight / 2), $white, $font, (string)$issue->getNumber());
    }

    /**
     * @param resource $image
     * @param int $red
     * @param int $green
     * @param int $blue
     *
     * @return int
     */
    private function createColor($image, $red, $green, $blue)
    {
        //get color from palette
        $color = imagecolorexact($image, $red, $green, $blue);
        if ($color === -1) {
            //color does not exist...
            //test if we have used up palette
            if (imagecolorstotal($image) >= 255) {
                //palette used up; pick closest assigned color
                $color = imagecolorclosest($image, $red, $green, $blue);
            } else {
                //palette NOT used up; assign new color
                $color = imagecolorallocate($image, $red, $green, $blue);
            }
        }

        return $color;
    }
}
