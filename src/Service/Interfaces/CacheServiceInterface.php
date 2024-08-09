<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\ConstructionSiteImage;
use App\Entity\IssueImage;
use App\Entity\MapFile;
use App\Entity\ProtocolEntryFile;

interface CacheServiceInterface
{
    public function warmUpCacheForIssueImage(IssueImage $issueImage): void;

    public function warmUpCacheForConstructionSiteImage(ConstructionSiteImage $constructionSiteImage): void;

    public function warmUpCacheForProtocolEntryFile(ProtocolEntryFile $protocolEntryFile): void;

    public function warmUpCacheForMapFile(MapFile $mapFile): void;
}
