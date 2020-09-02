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

use App\Entity\ConstructionSiteImage;
use App\Entity\IssueImage;
use App\Entity\MapFile;

interface CacheServiceInterface
{
    public function warmUpCacheForIssueImage(IssueImage $issueImage);

    public function warmUpCacheForConstructionSiteImage(ConstructionSiteImage $constructionSiteImage);

    public function warmUpCacheForMapFile(MapFile $mapFile);
}
