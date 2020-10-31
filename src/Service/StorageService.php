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
use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Entity\Traits\FileTrait;
use App\Helper\FileHelper;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\StorageServiceInterface;
use DateTime;
use const DIRECTORY_SEPARATOR;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StorageService implements StorageServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * UploadService constructor.
     */
    public function __construct(PathServiceInterface $pathService)
    {
        $this->pathService = $pathService;
    }

    public function setNewFolderName(ConstructionSite $constructionSite)
    {
        $rootFolder = $this->pathService->getRootFolderOfConstructionSites();
        $sanitizedFolderName = FileHelper::sanitizeFileName($constructionSite->getName());

        $counter = 0;
        do {
            $uniqueFolderName = $sanitizedFolderName;
            if ($counter++ > 0) {
                $uniqueFolderName .= $counter;
            }
        } while (is_dir($rootFolder.DIRECTORY_SEPARATOR.$uniqueFolderName));

        $constructionSite->setFolderName($uniqueFolderName);
    }

    public function uploadConstructionSiteImage(UploadedFile $file, ConstructionSite $constructionSite): ?ConstructionSiteImage
    {
        $targetFolder = $this->pathService->getFolderForConstructionSiteImages($constructionSite);
        $constructionSiteImage = new ConstructionSiteImage();
        if (!$this->uploadFile($file, $targetFolder, $constructionSiteImage)) {
            return null;
        }

        $constructionSiteImage->setConstructionSite($constructionSite);
        $constructionSite->setImage($constructionSiteImage);

        return $constructionSiteImage;
    }

    public function uploadMapFile(UploadedFile $file, Map $map): ?MapFile
    {
        $targetFolder = $this->pathService->getFolderForMapFiles($map->getConstructionSite());
        $mapFile = new MapFile();
        if (!$this->uploadFile($file, $targetFolder, $mapFile)) {
            return null;
        }

        $mapFile->getMaps()->add($map);
        $mapFile->setConstructionSite($map->getConstructionSite());
        $map->setFile($mapFile);

        return $mapFile;
    }

    public function uploadIssueImage(UploadedFile $file, Issue $issue): ?IssueImage
    {
        $targetFolder = $this->pathService->getFolderForIssueImages($issue->getMap()->getConstructionSite());
        $issueImage = new IssueImage();
        if (!$this->uploadFile($file, $targetFolder, $issueImage)) {
            return null;
        }

        $issueImage->setIssue($issue);
        $issue->setImage($issueImage);

        return $issueImage;
    }

    /**
     * @param FileTrait $entity
     */
    private function uploadFile(UploadedFile $file, string $targetFolder, $entity): bool
    {
        FileHelper::ensureFolderExists($targetFolder);
        $targetFileName = $this->getSanitizedUniqueFileName($targetFolder, $file->getClientOriginalName());
        if (!$file->move($targetFolder, $targetFileName)) {
            return false;
        }

        // write filetrait properties
        $targetPath = $targetFolder.DIRECTORY_SEPARATOR.$targetFileName;
        $hash = hash_file('sha256', $targetPath);
        $entity->setHash($hash);
        $entity->setFilename($targetFileName);

        return true;
    }

    private function getSanitizedUniqueFileName(string $targetFolder, string $targetFileName): string
    {
        $fileName = pathinfo($targetFileName, PATHINFO_FILENAME);
        $extension = pathinfo($targetFileName, PATHINFO_EXTENSION);

        $sanitizedFileName = FileHelper::sanitizeFileName($fileName).'.'.$extension;
        $targetPath = $targetFolder.DIRECTORY_SEPARATOR.$sanitizedFileName;
        if (!is_file($targetPath)) {
            return $sanitizedFileName;
        }

        $now = new DateTime();
        $counter = 0;
        do {
            $prefix = $sanitizedFileName.'_duplicate_'.$now->format('Y-m-d\THi');
            if ($counter++ > 0) {
                $prefix .= '_'.$counter;
            }
            $uniqueFileName = $prefix.'.'.$extension;
            $uniqueTargetPath = $targetFolder.DIRECTORY_SEPARATOR.$uniqueFileName;
        } while (file_exists($uniqueTargetPath));

        return $uniqueFileName;
    }
}
