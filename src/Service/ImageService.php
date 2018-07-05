<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Issue;
use App\Entity\Map;
use App\Service\Interfaces\ImageServiceInterface;

class ImageService implements ImageServiceInterface
{
    /**
     * @var string
     */
    private $pubFolder = __DIR__ . '/../../public';

    /**
     * @param Map $map
     * @param Issue[] $issues
     */
    private function render(Map $map, array $issues, $filePath)
    {
        //create folder
        $generationTargetFolder = $this->getGenerationTargetFolder($map);
        if (!file_exists($generationTargetFolder)) {
            mkdir($generationTargetFolder, 0777, true);
        }

        //compile pdf to image
        $renderedMapPath = $generationTargetFolder . '/render.jpg';
        if (!file_exists($renderedMapPath)) {
            $mapFilePath = $this->pubFolder . '/' . $map->getFilePath();
            $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=1920 -dDEVICEHEIGHTPOINTS=1080 -dJPEGQ=80 -dUseCropBox -dPDFFitPage -sPageList=1 -o ' . $renderedMapPath . ' ' . $mapFilePath;
            exec($command);
        }

        //open image file
        $sourceImage = imagecreatefromjpeg($renderedMapPath);

        //draw the issues on the map
        foreach ($issues as $issue) {
            $this->draw($issue, $sourceImage);
        }

        //write to disk & destroy
        imagejpeg($sourceImage, $filePath, 90);
        imagedestroy($sourceImage);
    }

    /**
     * @param Issue $issue
     * @param $image
     */
    private function draw(Issue $issue, &$image)
    {
        //get sizes
        $xSize = imagesx($image);
        $ySize = imagesy($image);

        //target location
        $yCoordinate = $issue->getPositionY() * $ySize;
        $xCoordinate = $issue->getPositionX() * $xSize;

        //colors sometime do not work and show up as black. just choose another color as close as possible to workaround
        if ($issue->getReviewedAt() !== null) {
            //green
            $circleColor = $this->createColor($image, 204, 255, 255);
        } else {
            //orange
            $circleColor = $this->createColor($image, 255, 204, 51);
        }

        $this->drawCircleWithText($yCoordinate, $xCoordinate, $circleColor, (string)$issue->getNumber(), $image);
    }

    /**
     * @param $yCoordinate
     * @param $xCoordinate
     * @param $circleColor
     * @param $text
     * @param $image
     */
    private function drawCircleWithText($yCoordinate, $xCoordinate, $circleColor, $text, &$image)
    {
        //get text size
        $font = __DIR__ . '/Resources/OpenSans-Regular.ttf';
        $fontSize = 20;
        $txtSize = imagettfbbox($fontSize, 0, $font, $text);
        $txtWidth = abs($txtSize[4] - $txtSize[0]);
        $txtHeight = abs($txtSize[5] - $txtSize[1]);

        //calculate diameter around text
        $buffer = 20;
        $diameter = max($txtWidth, $txtHeight) + $buffer;

        //draw white base ellipse before the colored one
        $white = $this->createColor($image, 255, 255, 255);
        imagefilledellipse($image, $xCoordinate, $yCoordinate, $diameter + 2, $diameter + 2, $white);
        imagefilledellipse($image, $xCoordinate, $yCoordinate, $diameter, $diameter, $circleColor);

        //draw text
        imagettftext($image, $fontSize, 0, $xCoordinate - ($txtWidth / 2), $yCoordinate + ($txtHeight / 2), $white, $font, $text);
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

    /**
     * @param Map $map
     * @param array $issues
     *
     * @return string
     */
    private function getFilePathFor(Map $map, array $issues)
    {
        //hash issue all used issue info
        $hash = hash('sha256',
            implode(
                ',',
                array_map(
                    function ($issue) {
                        /* @var Issue $issue */
                        return $issue->getId() . $issue->getStatusCode();
                    },
                    $issues)
            )
        );

        return $this->getGenerationTargetFolder($map) . '/' . $hash . '.jpg';
    }

    /**
     * @param Map $map
     *
     * @return string
     */
    private function getGenerationTargetFolder(Map $map)
    {
        return $this->pubFolder . '/generated/' . $map->getConstructionSite()->getId() . '/map/' . $map->getId();
    }

    /**
     * @param Map $map
     * @param array $issues
     *
     * @return string
     */
    public function generateMapImage(Map $map, array $issues)
    {
        $filePath = $this->getFilePathFor($map, $issues);
        if (!file_exists($filePath)) {
            $this->render($map, $issues, $filePath);
        }

        return $filePath;
    }
}
