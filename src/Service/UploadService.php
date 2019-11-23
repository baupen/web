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
use App\Entity\MapFile;
use App\Entity\Traits\FileTrait;
use App\Model\UploadFileCheck;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\UploadServiceInterface;
use DateTime;
use const DIRECTORY_SEPARATOR;
use Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;
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
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * UploadService constructor.
     */
    public function __construct(PathServiceInterface $pathService, ImageServiceInterface $imageService, RegistryInterface $registry)
    {
        $this->pathService = $pathService;
        $this->imageService = $imageService;
        $this->doctrine = $registry;
    }

    /**
     * @throws Exception
     *
     * @return IssueImage|null
     */
    public function uploadIssueImage(UploadedFile $file, Issue $issue, string $targetFileName)
    {
        $targetFolder = $this->pathService->getFolderForIssueImage($issue->getMap()->getConstructionSite());
        $issueImage = new IssueImage();
        if (!$this->uploadFile($file, $targetFolder, $targetFileName, $issueImage)) {
            return null;
        }

        $this->imageService->warmUpCacheForIssue($issue);

        return $issueImage;
    }

    /**
     * @throws Exception
     *
     * @return MapFile|null
     */
    public function uploadMapFile(UploadedFile $file, ConstructionSite $constructionSite, string $targetFileName)
    {
        $targetFolder = $this->pathService->getFolderForMapFile($constructionSite);
        $mapFile = new MapFile();
        if (!$this->uploadFile($file, $targetFolder, $targetFileName, $mapFile)) {
            return null;
        }

        return $mapFile;
    }

    /**
     * @throws Exception
     *
     * @return ConstructionSiteImage|null
     */
    public function uploadConstructionSiteImage(UploadedFile $file, ConstructionSite $constructionSite, string $targetFileName)
    {
        $targetFolder = $this->pathService->getFolderForConstructionSiteImage($constructionSite);
        $mapFile = new ConstructionSiteImage();
        if (!$this->uploadFile($file, $targetFolder, $targetFileName, $mapFile)) {
            return null;
        }

        $this->imageService->warmUpCacheForConstructionSite($constructionSite);

        return $mapFile;
    }

    /**
     * @throws Exception
     *
     * @return UploadFileCheck
     */
    public function checkUploadMapFile(string $hash, string $filename, ConstructionSite $constructionSite)
    {
        $result = new UploadFileCheck();
        $result->setUploadPossible(true);
        $result->setDerivedFileName($filename);

        //check if already exists
        $sameHashMapFiles = $this->doctrine->getRepository(MapFile::class)->findBy(['hash' => $hash, 'constructionSite' => $constructionSite->getId()]);

        $sameHash = [];
        foreach ($sameHashMapFiles as $sameHashMapFile) {
            $sameHash[] = $sameHashMapFile->getId();
        }
        $result->setSameHashConflicts($sameHash);

        $sameFilenameMapFile = $this->doctrine->getRepository(MapFile::class)->findOneBy(['filename' => $filename, 'constructionSite' => $constructionSite->getId()]);
        if ($sameFilenameMapFile !== null) {
            $result->setFileNameConflict($sameFilenameMapFile->getId());

            $targetFolder = $this->pathService->getFolderForMapFile($constructionSite);
            $targetFileName = $this->getCollisionProtectedFileName($targetFolder, $filename);
            if ($targetFileName !== null) {
                $result->setDerivedFileName($targetFileName);
            } else {
                $result->setUploadPossible(false);
            }
        }

        return $result;
    }

    /**
     * @param FileTrait $entity
     */
    private function uploadFile(UploadedFile $file, string $targetFolder, string $targetFileName, $entity): bool
    {
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }

        $targetPath = $targetFolder . DIRECTORY_SEPARATOR . $targetFileName;
        if (file_exists($targetPath)) {
            return false;
        }

        //move file
        if (!$file->move($targetFolder, $targetFileName)) {
            return false;
        }

        $this->writeFileTraitProperties($entity, $targetFolder, $targetFileName);

        return true;
    }

    /**
     * @param FileTrait $entity
     */
    private function writeFileTraitProperties($entity, string $targetFolder, string $targetFileName)
    {
        $entity->setFilename($targetFileName);
        $entity->setHash(hash_file('sha256', $targetFolder . DIRECTORY_SEPARATOR . $targetFileName));
        $entity->setDisplayFilename($targetFileName);
    }

    /**
     * @throws Exception
     *
     * @return string|null
     */
    private function getCollisionProtectedFileName(string $targetFolder, string $targetFileName)
    {
        $targetPath = $targetFolder . DIRECTORY_SEPARATOR . $targetFileName;
        if (is_file($targetPath)) {
            $extension = pathinfo($targetPath, PATHINFO_EXTENSION);
            $filename = pathinfo($targetPath, PATHINFO_FILENAME);

            $now = new DateTime();
            $targetFileName = $filename . '_duplicate_' . $now->format('Y-m-d\TH:i') . '.' . $extension;

            $targetPath = $targetFolder . DIRECTORY_SEPARATOR . $targetFileName;
            if (file_exists($targetPath)) {
                return null;
            }
        }

        return $targetFileName;
    }
}
