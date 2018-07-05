<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Report;

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;

class ReportDefinition extends PdfDefinition
{
    /**
     * @var ConstructionSite
     */
    private $constructionSite;
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var Map[]
     */
    private $maps = [];

    /**
     * @var Issue[][]
     */
    private $issuesByMap = [];

    /**
     * @var string[]
     */
    private $imageByMap = [];

    /**
     * ReportDefinition constructor.
     *
     * @param ConstructionSite $constructionSite
     * @param Filter $filter
     * @param string $author
     */
    public function __construct(ConstructionSite $constructionSite, Filter $filter, string $author)
    {
        parent::__construct($constructionSite->getName(), $author, __DIR__ . '/../../public/files/report_logo.svg');
    }

    /**
     * @param Map $map
     * @param array $issues
     * @param null|string $imagePath
     */
    public function addMap(Map $map, array $issues, ?string $imagePath)
    {
        $this->maps[$map->getId()] = $map;
        $this->imageByMap[$map->getId()] = $imagePath;
        $this->issuesByMap[$map->getId()] = $issues;
    }

    /**
     * @return ConstructionSite
     */
    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    /**
     * @return Filter
     */
    public function getFilter(): Filter
    {
        return $this->filter;
    }
}
