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

class AggregatedIssuesContent
{
    /**
     * @var string
     */
    private $tableDescription;

    /**
     * @var string[]
     */
    private $identifierHeader;

    /**
     * @var string[]
     */
    private $identifierContent = [];

    /**
     * @var string[]
     */
    private $issuesHeader;

    /**
     * @var string[]
     */
    private $issuesContent;

    public function getTableDescription(): string
    {
        return $this->tableDescription;
    }

    public function setTableDescription(string $tableDescription): void
    {
        $this->tableDescription = $tableDescription;
    }

    /**
     * @return string[]
     */
    public function getIdentifierHeader(): array
    {
        return $this->identifierHeader;
    }

    /**
     * @param string[] $identifierHeader
     */
    public function setIdentifierHeader(array $identifierHeader): void
    {
        $this->identifierHeader = $identifierHeader;
    }

    /**
     * @return string[]
     */
    public function getIdentifierContent(): array
    {
        return $this->identifierContent;
    }

    /**
     * @param string[] $identifierContent
     */
    public function addIdentifierContent(array $identifierContent): void
    {
        $this->identifierContent[] = $identifierContent;
    }

    /**
     * @return string[]
     */
    public function getIssuesHeader(): array
    {
        return $this->issuesHeader;
    }

    /**
     * @param string[] $issuesHeader
     */
    public function setIssuesHeader(array $issuesHeader): void
    {
        $this->issuesHeader = $issuesHeader;
    }

    /**
     * @return string[]
     */
    public function getIssuesContent(): array
    {
        return $this->issuesContent;
    }

    /**
     * @param string[] $issuesContent
     */
    public function setIssuesContent(array $issuesContent): void
    {
        $this->issuesContent = $issuesContent;
    }
}
