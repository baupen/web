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
use App\Entity\Map;

interface PathServiceInterface
{
    /**
     * @return string
     */
    public function getFolderRoot();

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getFolderForConstructionSite(ConstructionSite $constructionSite);

    /**
     * @param Map $map
     *
     * @return string
     */
    public function getFolderForMap(Map $map);

    /**
     * @param Issue $issue
     *
     * @return string
     */
    public function getFolderForIssue(Issue $issue);

    /**
     * @return string
     */
    public function getTransientFolderRoot();

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getTransientFolderForConstructionSite(ConstructionSite $constructionSite);

    /**
     * @param Map $map
     *
     * @return string
     */
    public function getTransientFolderForMap(Map $map);

    /**
     * @param Issue $issue
     *
     * @return string
     */
    public function getTransientFolderForIssue(Issue $issue);

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return string
     */
    public function getTransientFolderForReports(ConstructionSite $constructionSite);
}
