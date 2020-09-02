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

use App\Entity\Base\BaseEntity;
use App\Entity\ConstructionSite;
use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Entity\Traits\FileTrait;
use App\Helper\FileHelper;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\UploadServiceInterface;
use DateTime;
use const DIRECTORY_SEPARATOR;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService implements UploadServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * UploadService constructor.
     */
    public function __construct(PathServiceInterface $pathService, ManagerRegistry $doctrine)
    {
        $this->pathService = $pathService;
        $this->doctrine = $doctrine;
    }

    public function uploadConstructionSiteImage(UploadedFile $file, ConstructionSite $constructionSite): ?ConstructionSiteImage
    {
        $targetFolder = $this->pathService->getFolderForConstructionSiteImages($constructionSite);
        $constructionSiteImage = new ConstructionSiteImage();
        if (!$this->uploadFile($file, $targetFolder, $constructionSiteImage)) {
            return null;
        }

        $constructionSiteImage->setConstructionSite($constructionSite);
        $constructionSite->getImages()->add($constructionSiteImage);
        $constructionSite->setImage($constructionSiteImage);
        $this->persist($constructionSiteImage);

        return $constructionSiteImage;
    }

    public function uploadMapFile(UploadedFile $file, Map $map): ?MapFile
    {
        $targetFolder = $this->pathService->getFolderForMapFiles($map->getConstructionSite());
        $mapFile = new MapFile();
        if (!$this->uploadFile($file, $targetFolder, $mapFile)) {
            return null;
        }

        $mapFile->setMap($map);
        $map->getFiles()->add($mapFile);
        $map->setFile($mapFile);
        $this->persist($mapFile);

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
        $issue->getImages()->add($issueImage);
        $issue->setImage($issueImage);
        $this->persist($issueImage);

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
        $sanitizedFileName = preg_replace('/[^a-z0-9]+/', '-', strtolower($targetFileName));
        $targetPath = $targetFolder.DIRECTORY_SEPARATOR.$sanitizedFileName;
        if (!is_file($targetPath)) {
            return $sanitizedFileName;
        }

        $extension = pathinfo($targetPath, PATHINFO_EXTENSION);
        $filename = pathinfo($targetPath, PATHINFO_FILENAME);

        $now = new DateTime();

        return $filename.'_duplicate_'.$now->format('Y-m-d\THi').'.'.$extension;
    }

    private function persist(BaseEntity $entity): void
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($entity);
        $manager->flush();
    }
}
