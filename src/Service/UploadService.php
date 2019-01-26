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
use App\Model\UploadFileCheck;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\UploadServiceInterface;
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
     *
     * @param PathServiceInterface $pathService
     * @param ImageServiceInterface $imageService
     * @param RegistryInterface $registry
     */
    public function __construct(PathServiceInterface $pathService, ImageServiceInterface $imageService, RegistryInterface $registry)
    {
        $this->pathService = $pathService;
        $this->imageService = $imageService;
        $this->doctrine = $registry;
    }

    /**
     * @param UploadedFile $file
     * @param Issue $issue
     * @param string $targetFileName
     *
     * @throws \Exception
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

        $this->imageService->warmUpCacheForIssue($issue);

        $issueImage = new IssueImage();
        $this->writeFileTraitProperties($issueImage, $targetFolder, $targetFileName);

        return $issueImage;
    }

    /**
     * @param UploadedFile $file
     * @param ConstructionSite $constructionSite
     * @param string $targetFileName
     *
     * @throws \Exception
     *
     * @return MapFile|null
     */
    public function uploadMapFile(UploadedFile $file, ConstructionSite $constructionSite, string $targetFileName)
    {
        //create folder
        $targetFolder = $this->pathService->getFolderForMapFile($constructionSite);
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }

        $targetPath = $targetFolder . \DIRECTORY_SEPARATOR . $targetFileName;
        if (file_exists($targetPath)) {
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
     * @throws \Exception
     *
     * @return string|null
     */
    private function getCollisionProtectedFileName(string $targetFolder, string $targetFileName)
    {
        $targetPath = $targetFolder . \DIRECTORY_SEPARATOR . $targetFileName;
        if (is_file($targetPath)) {
            $extension = pathinfo($targetPath, PATHINFO_EXTENSION);
            $filename = pathinfo($targetPath, PATHINFO_FILENAME);

            $now = new \DateTime();
            $targetFileName = $filename . '_duplicate_' . $now->format('Y-m-d\TH:i') . '.' . $extension;

            $targetPath = $targetFolder . \DIRECTORY_SEPARATOR . $targetFileName;
            if (file_exists($targetPath)) {
                return null;
            }
        }

        return $targetFileName;
    }

    /**
     * @param string $hash
     * @param string $filename
     * @param ConstructionSite $constructionSite
     *
     * @throws \Exception
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
}
