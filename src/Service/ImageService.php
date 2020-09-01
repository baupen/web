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

use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Service\Image\GdService;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use function count;
use const DIRECTORY_SEPARATOR;
use function in_array;

class ImageService implements ImageServiceInterface
{
    /**
     * the name of the image rendered from the map pdf.
     */
    private const MAP_RENDER_NAME = 'render.jpg';

    /**
     * @var array
     */
    private $validSizes = [ImageServiceInterface::SIZE_THUMBNAIL, ImageServiceInterface::SIZE_PREVIEW, ImageServiceInterface::SIZE_REPORT_MAP];

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var GdService
     */
    private $gdService;

    /**
     * @return bool
     */
    public function isValidSize(string $uncheckedSize)
    {
        return in_array($uncheckedSize, $this->validSizes, true);
    }

    public function resizeIssueImage(IssueImage $issueImage, string $size = self::SIZE_THUMBNAIL): ?string
    {
        //setup paths
        $sourceFolder = $this->pathService->getFolderForIssueImage($issueImage);
        $targetFolder = $this->pathService->getTransientFolderForIssueImage($issueImage);
        $this->ensureFolderExists($targetFolder);

        return $this->renderSizeFor($issueImage->getFilename(), $sourceFolder, $targetFolder, $size);
    }

    public function resizeConstructionSiteImage(ConstructionSiteImage $constructionSiteImage, string $size = self::SIZE_THUMBNAIL): ?string
    {
        //setup paths
        $sourceFolder = $this->pathService->getFolderForConstructionSiteImage($constructionSiteImage);
        $targetFolder = $this->pathService->getTransientFolderForConstructionSiteImage($constructionSiteImage);
        $this->ensureFolderExists($targetFolder);

        return $this->renderSizeFor($constructionSiteImage->getFilename(), $sourceFolder, $targetFolder, $size);
    }

    /**
     * @param Issue[] $issues
     * @param string  $size
     */
    public function renderMapFileWithIssues(MapFile $mapFile, array $issues, $size = self::SIZE_THUMBNAIL): ?string
    {
        //setup paths
        $sourceFilePath = $this->pathService->getFolderForMapFile($mapFile).DIRECTORY_SEPARATOR.$mapFile->getFilename();
        $generationTargetFolder = $this->pathService->getTransientFolderForMapFile($mapFile);
        $this->ensureFolderExists($generationTargetFolder);

        return $this->generateMapImageInternal($issues, $sourceFilePath, $generationTargetFolder, false, $size);
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForIssueImage(IssueImage $issueImage)
    {
        foreach ($this->validSizes as $validSize) {
            $this->resizeIssueImage($issueImage, $validSize);
        }
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForConstructionSiteImage(ConstructionSiteImage $constructionSiteImage)
    {
        foreach ($this->validSizes as $validSize) {
            $this->resizeConstructionSiteImage($constructionSiteImage, $validSize);
        }
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForMapFile(MapFile $mapFile)
    {
        foreach ($this->validSizes as $validSize) {
            $this->renderMapFileWithIssues($mapFile, [], $validSize);
        }
    }

    /**
     * @param $size
     *
     * @return string
     */
    private function renderSizeFor(?string $sourceFileName, string $sourceFolder, string $targetFolder, $size)
    {
        //setup paths
        $sourceFilePath = $sourceFolder.DIRECTORY_SEPARATOR.$sourceFileName;
        $targetFileName = $this->getSizeFilename($sourceFileName, $size);
        $targetFilePath = $targetFolder.DIRECTORY_SEPARATOR.$targetFileName;

        if (!file_exists($targetFilePath)) {
            $this->renderSizeOfImage($sourceFilePath, $targetFilePath, $size);

            //abort if generation failed
            if (!file_exists($targetFilePath)) {
                return null;
            }
        }

        return $targetFilePath;
    }

    /**
     * adds sizing infos to filename.
     */
    private function getSizeFilename(string $fileName, string $size): string
    {
        $ending = pathinfo($fileName, PATHINFO_EXTENSION);
        $filenameWithoutEnding = mb_substr($fileName, 0, -(mb_strlen($ending) + 1));

        return $filenameWithoutEnding.'_'.$size.'.'.$ending;
    }

    /**
     * @param string $size
     */
    private function renderSizeOfImage(string $sourceFilePath, string $targetFilePath, $size = ImageServiceInterface::SIZE_THUMBNAIL)
    {
        //generate variant if possible
        switch ($size) {
            case ImageServiceInterface::SIZE_THUMBNAIL:
                $this->gdService->resizeImage($sourceFilePath, $targetFilePath, 100, 80);
                break;
            case ImageServiceInterface::SIZE_PREVIEW:
                $this->gdService->resizeImage($sourceFilePath, $targetFilePath, 600, 877);
                break;
            case ImageServiceInterface::SIZE_REPORT_MAP:
                $this->gdService->resizeImage($sourceFilePath, $targetFilePath, 2480, 3508);
                break;
        }
    }

    /**
     * @param $image
     */
    private function drawIssue(Issue $issue, bool $rotated, &$image)
    {
        //get sizes
        $xSize = imagesx($image);
        $ySize = imagesy($image);

        //target location
        $position = $issue->getPosition();
        if ($rotated) {
            $yCoordinate = $position->getPositionX();
            $xCoordinate = $position->getPositionY();
        } else {
            $yCoordinate = $position->getPositionY();
            $xCoordinate = $position->getPositionX();
        }
        $yCoordinate *= $ySize;
        $xCoordinate *= $xSize;

        //colors sometime do not work and show up as black. just choose another color as close as possible to workaround
        if (null !== $issue->getReviewedAt()) {
            //green
            $circleColor = 'green';
        } else {
            //orange
            $circleColor = 'orange';
        }

        $this->gdService->drawRectangleWithText($yCoordinate, $xCoordinate, $circleColor, (string) $issue->getNumber(), $image);
    }

    /**
     * render issues on image if it does not already exist.
     *
     * @param Issue[] $issues
     * @param string  $pdfRenderPath
     * @param string  $issueImagePath
     * @param string  $landscapeIssueImagePath
     * @param bool    $forceLandscape
     *
     * @return string
     */
    private function renderIssues(array $issues, $pdfRenderPath, $issueImagePath, $landscapeIssueImagePath, $forceLandscape)
    {
        $targetImagePath = null;
        $sourceImageStream = null;
        $rotated = false;
        if ($forceLandscape) {
            if (!is_file($landscapeIssueImagePath)) {
                $targetImagePath = $landscapeIssueImagePath;
                $sourceImageStream = imagecreatefromjpeg($pdfRenderPath);
                $width = imagesx($sourceImageStream);
                $height = imagesy($sourceImageStream);

                if ($height > $width) {
                    $sourceImageStream = imagerotate($sourceImageStream, 90, 0);
                    $rotated = true;
                } elseif (file_exists($issueImagePath)) {
                    //simply copy already rendered file
                    copy($issueImagePath, $landscapeIssueImagePath);
                    imagedestroy($sourceImageStream);
                    $sourceImageStream = null;
                }
            }
        } elseif (!is_file($issueImagePath)) {
            $targetImagePath = $issueImagePath;
            $sourceImageStream = imagecreatefromjpeg($pdfRenderPath);
        }

        //render if needed
        if (null !== $sourceImageStream) {
            //draw the issues on the map
            foreach ($issues as $issue) {
                if (null !== $issue->getPosition()) {
                    $this->drawIssue($issue, $rotated, $sourceImageStream);
                }
            }

            //write to disk & destroy
            imagejpeg($sourceImageStream, $targetImagePath, 90);
            imagedestroy($sourceImageStream);
        }

        return $forceLandscape ? $landscapeIssueImagePath : $issueImagePath;
    }

    /**
     * @param Issue[] $issues
     * @param bool    $forceLandscape
     * @param string  $size
     *
     * @return string|null
     */
    private function generateMapImageInternal(array $issues, string $sourceFilePath, string $generationTargetFolder, $forceLandscape, $size)
    {
        //render pdf to image
        $pdfRenderPath = $generationTargetFolder.DIRECTORY_SEPARATOR.self::MAP_RENDER_NAME;
        if (!file_exists($pdfRenderPath)) {
            $this->renderPdfToImage($sourceFilePath, $pdfRenderPath);

            //abort if creation failed
            if (!file_exists($pdfRenderPath)) {
                return null;
            }
        }

        // shortcut if no issues to be printed
        if (count($issues) > 0) {
            //prepare filename for exact issue combination
            $issueToString = function ($issue) {
                /* @var Issue $issue */
                return $issue->getId().$issue->getStatusCode().$issue->getLastChangedAt()->format('c');
            };
            $issueHash = hash('sha256', 'v1'.implode(',', array_map($issueToString, $issues)));

            //render issue image
            $issueImagePath = $generationTargetFolder.DIRECTORY_SEPARATOR.$issueHash.'.jpg';
            $landscapeIssueImagePath = $generationTargetFolder.DIRECTORY_SEPARATOR.$issueHash.'_landscape.jpg';
            $issueRenderPath = $this->renderIssues($issues, $pdfRenderPath, $issueImagePath, $landscapeIssueImagePath, $forceLandscape);
        } else {
            $issueRenderPath = $pdfRenderPath;
        }

        //render size variant
        $fileName = pathinfo($issueRenderPath, PATHINFO_BASENAME);
        $issueImagePathSize = $generationTargetFolder.DIRECTORY_SEPARATOR.$this->getSizeFilename($fileName, $size);
        if (!is_file($issueImagePathSize)) {
            $this->renderSizeOfImage($issueRenderPath, $issueImagePathSize, $size);

            //abort if creation failed
            if (!is_file($issueImagePathSize)) {
                return null;
            }
        }

        //return the path of the rendered file
        return $issueImagePathSize;
    }

    private function ensureFolderExists(string $folderName)
    {
        if (!is_dir($folderName)) {
            mkdir($folderName, 0777, true);
        }
    }
}
