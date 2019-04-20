<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Model;

class MapContent
{
    /**
     * @var string
     */
    private $mapName;

    /**
     * @var string
     */
    private $mapContext;

    /**
     * @var string|null
     */
    private $mapImage;

    /**
     * @var string[]
     */
    private $issuesTableHeader;

    /**
     * @var string[]
     */
    private $issuesTableContent;

    /**
     * @var IssueImage[]
     */
    private $issueImages = [];

    /**
     * @return string
     */
    public function getMapName(): string
    {
        return $this->mapName;
    }

    /**
     * @param string $mapName
     */
    public function setMapName(string $mapName): void
    {
        $this->mapName = $mapName;
    }

    /**
     * @return string
     */
    public function getMapContext(): string
    {
        return $this->mapContext;
    }

    /**
     * @param string $mapContext
     */
    public function setMapContext(string $mapContext): void
    {
        $this->mapContext = $mapContext;
    }

    /**
     * @return string|null
     */
    public function getMapImage(): ?string
    {
        return $this->mapImage;
    }

    /**
     * @param string|null $mapImage
     */
    public function setMapImage(?string $mapImage): void
    {
        $this->mapImage = $mapImage;
    }

    /**
     * @return string[]
     */
    public function getIssuesTableHeader(): array
    {
        return $this->issuesTableHeader;
    }

    /**
     * @param string[] $issuesTableHeader
     */
    public function setIssuesTableHeader(array $issuesTableHeader): void
    {
        $this->issuesTableHeader = $issuesTableHeader;
    }

    /**
     * @return string[]
     */
    public function getIssuesTableContent(): array
    {
        return $this->issuesTableContent;
    }

    /**
     * @param string[] $issuesTableContent
     */
    public function setIssuesTableContent(array $issuesTableContent): void
    {
        $this->issuesTableContent = $issuesTableContent;
    }

    /**
     * @param IssueImage $issueImage
     */
    public function addIssueImage(IssueImage $issueImage)
    {
        $this->issueImages[] = $issueImage;
    }

    /**
     * @return IssueImage[]
     */
    public function getIssueImages(): array
    {
        return $this->issueImages;
    }
}
