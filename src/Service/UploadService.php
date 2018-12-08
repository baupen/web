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

use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\MapFile;
use App\Entity\Traits\FileTrait;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\UploadServiceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService implements UploadServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var ImageServiceInterface
     */
    private $imageService;

    /**
     * UploadService constructor.
     *
     * @param PathServiceInterface $pathService
     * @param ImageServiceInterface $imageService
     */
    public function __construct(PathServiceInterface $pathService, ImageServiceInterface $imageService)
    {
        $this->pathService = $pathService;
        $this->imageService = $imageService;
    }

    /**
     * @param UploadedFile $file
     * @param Issue $issue
     * @param string $targetFileName
     *
     * @return IssueImage|null
     */
    public function uploadIssueImage(UploadedFile $file, Issue $issue, string $targetFileName)
    {
        //create folder
        $targetFolder = $this->pathService->getFolderForIssueImage($issue->getMap()->getConstructionSite());
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }

        //ensure nothing is overridden
        $targetFileName = $this->getCollisionProtectedFileName($targetFolder, $targetFileName);
        if ($targetFileName === null) {
            return null;
        }

        //move file
        if (!$file->move($targetFolder, $targetFileName)) {
            return null;
        }

        $this->imageService->warmupCacheForIssue($issue);

        $issueImage = new IssueImage();
        $this->writeFileTraitProperties($issueImage, $targetFolder, $targetFileName);

        return $issueImage;
    }

    /**
     * @param UploadedFile $file
     * @param ConstructionSite $constructionSite
     *
     * @throws \Exception
     *
     * @return MapFile|null
     */
    public function uploadMapFile(UploadedFile $file, ConstructionSite $constructionSite)
    {
        //create folder
        $targetFolder = $this->pathService->getFolderForMapFile($constructionSite);
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }

        //get default file name
        $targetFileName = $file->getClientOriginalName();
        if ($targetFileName === null) {
            $targetFileName = (new \DateTime())->format('Y-m-d H-i-s') . '.pdf';
        }

        // ensure nothing is overriden
        $targetFileName = $this->getCollisionProtectedFileName($targetFolder, $targetFileName);
        if ($targetFileName === null) {
            return null;
        }

        //move file
        if (!$file->move($targetFolder, $targetFileName)) {
            return null;
        }

        $mapFile = new MapFile();
        $this->writeFileTraitProperties($mapFile, $targetFolder, $targetFileName);

        return $mapFile;
    }

    /**
     * @param FileTrait $entity
     * @param string $targetFolder
     * @param string $targetFileName
     */
    private function writeFileTraitProperties($entity, string $targetFolder, string $targetFileName)
    {
        $entity->setFilename($targetFileName);
        $entity->setHash(hash_file('sha256', $targetFolder . \DIRECTORY_SEPARATOR . $targetFileName));
        $entity->setDisplayFilename($targetFileName);
    }

    /**
     * @param string $targetFolder
     * @param string $targetFileName
     *
     * @return null|string
     */
    private function getCollisionProtectedFileName(string $targetFolder, string $targetFileName)
    {
        $targetPath = $targetFolder . \DIRECTORY_SEPARATOR . $targetFileName;
        if (is_file($targetPath)) {
            $extension = pathinfo($targetPath, PATHINFO_EXTENSION);
            $filename = pathinfo($targetPath, PATHINFO_FILENAME);

            // try at most 100 times
            $successful = false;
            for ($i = 1; $i < 100; ++$i) {
                $targetFileName = $filename . $i . '.' . $extension;
                $targetPath = $targetFolder . \DIRECTORY_SEPARATOR . $targetFileName;
                if (!is_file($targetPath)) {
                    $successful = true;
                    break;
                }
            }

            if (!$successful) {
                return null;
            }
        }

        return $targetFileName;
    }
}
