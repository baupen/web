<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use App\Controller\Base\BaseDoctrineController;
use App\Entity\Building;
use App\Entity\BuildingMap;
use App\Entity\Craftsman;
use App\Entity\Marker;
use App\Model\BuildingMap\BuildingMapMarkerInfo;
use Imagick;
use ImagickDraw;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/public")
 *
 * @return Response
 */
class PublicController extends BaseDoctrineController
{
    /**
     * @return Imagick
     * @throws \ImagickException
     */
    private function getImagickInstance()
    {
        $im = new Imagick();
        Imagick::setResourceLimit(Imagick::RESOURCETYPE_DISK, 1024 * 1024); //max 1GB
        Imagick::setResourceLimit(Imagick::RESOURCETYPE_TIME, 60); //max 5 seconds

        return $im;
    }

    /**
     * @Route("/render/{marker}", name="public_render")
     * @param Marker $marker
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \ImagickException
     */
    public function renderAction(Marker $marker)
    {
        $folder = __DIR__ . "/../../public/upload/";
        $mapFileName = $marker->getBuildingMap()->getFileName();
        $mapFilePath = $folder . $mapFileName;
        $imageFilePath = $folder . "render_" . $mapFileName . ".jpg";
        if (!file_exists($imageFilePath)) {
            exec("gs -sDEVICE=jpeg -r300 -dJPEGQ=80 -dUseCropBox -o " . $imageFilePath . " " . $mapFilePath);
        }

        $fileName = md5(
            $marker->getFrameYLength() . $marker->getFrameXHeight() . $marker->getFrameYPercentage() . $marker->getFrameXPercentage() .
            $marker->getMarkXPercentage() . $marker->getMarkYPercentage()
        );
        $renderFilename = $folder . "render_" . $mapFileName . "_" . $fileName . ".jpg";

        if (!file_exists($renderFilename) || true) {
            $image = $this->getImagickInstance();
            $image->readImage($imageFilePath);

            $width = $image->getImageGeometry()["width"];
            $height = $image->getImageGeometry()["height"];

            $newWidth = $width * $marker->getFrameYLength();
            $newHeight = $height * $marker->getFrameXHeight();

            $xShift = $width * $marker->getFrameXPercentage();
            $yShift = $height * $marker->getFrameYPercentage();

            $image->cropImage(
                (int)($newWidth),
                (int)($newHeight),
                (int)($xShift),
                (int)($yShift)
            );


            $xPos = $width * $marker->getMarkXPercentage() - $xShift;
            $yPos = $height * $marker->getMarkYPercentage() - $yShift;

            $total = $newWidth + $newHeight;

            $sensibleNumber = function ($relative) use ($total) {
                return (int)($total / 200 * $relative);
            };

            $color = "rgb(64, 48, 117)";
            $fillColor = new \ImagickPixel($color);

            $draw = new ImagickDraw();
            $draw->circle($xPos, $yPos, $xPos + $sensibleNumber(1), $yPos);
            $draw->setFillColor($fillColor);

            $image->drawImage($draw);

            $draw = new ImagickDraw();
            $draw->circle($xPos, $yPos, $xPos + $sensibleNumber(6), $yPos);
            $draw->setStrokeColor($fillColor);
            $draw->setStrokeWidth(1);
            $draw->setStrokeOpacity(1);
            $draw->setFillOpacity(0);

            $image->drawImage($draw);


            $image->writeImage($renderFilename);
        }

        return $this->file($renderFilename);
    }

    /**
     * @Route("/{guid}", name="public_view")
     * @param $guid
     * @return Response
     */
    public function viewAction($guid)
    {
        $markers = $this->getMarkers($guid);
        return $this->viewMarkers($markers);
    }


    /**
     * @Route("/{guid}/print", name="public_view_print")
     * @param $guid
     * @return Response
     */
    public function viewPrintAction($guid)
    {
        $markers = $this->getMarkers($guid);
        return $this->printMarkers($markers);
    }

    /**
     * @Route("/{guid}/{guid2}", name="public_view_2")
     * @param $guid
     * @param $guid2
     * @return Response
     */
    public function viewDoubleAction($guid, $guid2)
    {
        $markers = $this->getMarkersForDouble($guid, $guid2);
        return $this->viewMarkers($markers);
    }

    /**
     * @Route("/{guid}/{guid2}/print", name="public_view_2_print")
     * @param $guid
     * @param $guid2
     * @return Response
     */
    public function viewDoublePrintAction($guid, $guid2)
    {
        $markers = $this->getMarkersForDouble($guid, $guid2);
        return $this->printMarkers($markers);
    }

    /**
     * @param $guid
     * @param $guid2
     * @return array|Response
     */
    private function getMarkersForDouble($guid, $guid2)
    {
        //guid from building, guid2 from craftsman
        $building = $this->getDoctrine()->getRepository(Building::class)->findOneBy(["publicIdentifier" => $guid]);
        if ($building == null || !$building->isAccessible()) {
            return $this->notAccessibleError();
        }
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(["id" => $guid2]);
        if ($craftsman == null) {
            return $this->notAccessibleError();
        }
        $markers = [];
        foreach ($building->getMarkers() as $marker) {
            if ($craftsman->getId() == $marker->getCraftsman()->getId()) {
                $markers[] = $marker;
            }
        }
        return $markers;
    }

    /**
     * @param $guid
     * @return Marker[]|Response
     */
    private function getMarkers($guid)
    {

        //probably a map
        $map = $this->getDoctrine()->getRepository(BuildingMap::class)->findOneBy(["publicIdentifier" => $guid]);
        if ($map == null || !$map->isAccessible()) {
            return $this->notAccessibleError();
        }

        /* @var Marker[] $markers */
        $markers = $map->getMarkers()->toArray();
        return $markers;
    }

    /**
     * @param Marker[]|Response $markers
     * @return Response
     */
    private function viewMarkers($markers)
    {
        if ($markers instanceof Response) {
            return $markers;
        }

        /* @var Marker[] $pendingMarkers */
        $pendingMarkers = [];
        /* @var Marker[] $approvedMarkers */
        $approvedMarkers = [];
        foreach ($markers as $marker) {
            if ($marker->getApproved()) {
                $approvedMarkers[] = $marker;
            } else {
                $pendingMarkers[] = $marker;
            }
        }

        return $this->render("public/view.html.twig", ["pending_markers" => $pendingMarkers, "approved_markers" => $approvedMarkers]);
    }

    /**
     * @param Marker[]|Response $markers
     * @return Response
     */
    private function printMarkers($markers)
    {
        if ($markers instanceof Response) {
            return $markers;
        }

        /* @var BuildingMapMarkerInfo[] $mapMarkerInfo */
        $mapMarkerInfo = [];
        foreach ($markers as $marker) {
            if (!isset($mapMarkerInfo[$marker->getId()])) {
                $new = new BuildingMapMarkerInfo();
                $new->setBuildingMap($marker->getBuildingMap());
                $mapMarkerInfo[$marker->getId()] = $new;
            }
            $info = $mapMarkerInfo[$marker->getId()];
            $info->addMarker($marker);
        }

        return $this->render("public/print.html.twig", ["map_info" => $mapMarkerInfo]);
    }

    private function notAccessibleError()
    {
        return $this->render("public/not_accessible.html.twig");
    }
}
