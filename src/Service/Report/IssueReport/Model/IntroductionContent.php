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

class IntroductionContent
{
    /**
     * @var string
     */
    private $constructionSiteName;

    /**
     * @var string?
     */
    private $constructionSiteImage;

    /**
     * @var string[]
     */
    private $constructionSiteAddressLines;

    /**
     * @var string[]
     */
    private $reportElements;

    /**
     * @var string[]
     */
    private $filterEntries;

    /**
     * @return string
     */
    public function getConstructionSiteName(): string
    {
        return $this->constructionSiteName;
    }

    /**
     * @param string $constructionSiteName
     */
    public function setConstructionSiteName(string $constructionSiteName): void
    {
        $this->constructionSiteName = $constructionSiteName;
    }

    /**
     * @return string
     */
    public function getConstructionSiteImage(): string
    {
        return $this->constructionSiteImage;
    }

    /**
     * @param string $constructionSiteImage
     */
    public function setConstructionSiteImage(string $constructionSiteImage): void
    {
        $this->constructionSiteImage = $constructionSiteImage;
    }

    /**
     * @return string[]
     */
    public function getConstructionSiteAddressLines(): array
    {
        return $this->constructionSiteAddressLines;
    }

    /**
     * @param string[] $constructionSiteAddressLines
     */
    public function setConstructionSiteAddressLines(array $constructionSiteAddressLines): void
    {
        $this->constructionSiteAddressLines = $constructionSiteAddressLines;
    }

    /**
     * @return string[]
     */
    public function getReportElements(): array
    {
        return $this->reportElements;
    }

    /**
     * @param string[] $reportElements
     */
    public function setReportElements(array $reportElements): void
    {
        $this->reportElements = $reportElements;
    }

    /**
     * @return string[]
     */
    public function getFilterEntries(): array
    {
        return $this->filterEntries;
    }

    /**
     * @param string[] $filterEntries
     */
    public function setFilterEntries(array $filterEntries): void
    {
        $this->filterEntries = $filterEntries;
    }
}
