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
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\MapFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadServiceInterface
{
    /**
     * @param UploadedFile $file
     * @param Issue $issue
     * @param string $targetFileName
     *
     * @return IssueImage|null
     */
    public function uploadIssueImage(UploadedFile $file, Issue $issue, string $targetFileName);

    /**
     * @param UploadedFile $file
     * @param ConstructionSite $constructionSite
     *
     * @return MapFile|null
     */
    public function uploadMapFile(UploadedFile $file, ConstructionSite $constructionSite);
}
