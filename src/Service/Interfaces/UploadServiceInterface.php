<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\ConstructionSite;
use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\MapFile;
use App\Model\UploadFileCheck;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadServiceInterface
{
    /**
     * @param UploadedFile $file
     * @param Issue        $issue
     * @param string       $targetFileName
     *
     * @return IssueImage|null
     */
    public function uploadIssueImage(UploadedFile $file, Issue $issue, string $targetFileName);

    /**
     * @param string           $hash
     * @param string           $filename
     * @param ConstructionSite $constructionSite
     *
     * @return UploadFileCheck
     */
    public function checkUploadMapFile(string $hash, string $filename, ConstructionSite $constructionSite);

    /**
     * @param UploadedFile     $file
     * @param ConstructionSite $constructionSite
     * @param string           $targetFileName
     *
     * @return MapFile|null
     */
    public function uploadMapFile(UploadedFile $file, ConstructionSite $constructionSite, string $targetFileName);

    /**
     * @param UploadedFile     $file
     * @param ConstructionSite $constructionSite
     * @param string           $targetFileName
     *
     * @return ConstructionSiteImage|null
     */
    public function uploadConstructionSiteImage(UploadedFile $file, ConstructionSite $constructionSite, string $targetFileName);
}
