<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Response\Data;

use App\Api\External\Entity\ConstructionSite;
use App\Api\External\Entity\Craftsman;
use App\Api\External\Entity\Issue;
use App\Api\External\Entity\Map;
use App\Api\External\Entity\User;

class ReadData
{
    /**
     * @var Craftsman[]
     */
    private $changedCraftsmen;

    /**
     * @var string[]
     */
    private $removedCraftsmanIDs;

    /**
     * @var ConstructionSite[]
     */
    private $changedConstructionSites;

    /**
     * @var string[]
     */
    private $removedConstructionSiteIDs;

    /**
     * @var Map[]
     */
    private $changedMaps;

    /**
     * @var string[]
     */
    private $removedMapIDs;

    /**
     * @var Issue[]
     */
    private $changedIssues;

    /**
     * @var string[]
     */
    private $removedIssueIDs;

    /**
     * @var User|null
     */
    private $changedUser;

    /**
     * @return Craftsman[]
     */
    public function getChangedCraftsmen(): array
    {
        return $this->changedCraftsmen;
    }

    /**
     * @param Craftsman[] $changedCraftsmen
     */
    public function setChangedCraftsmen(array $changedCraftsmen): void
    {
        $this->changedCraftsmen = $changedCraftsmen;
    }

    /**
     * @return string[]
     */
    public function getRemovedCraftsmanIDs(): array
    {
        return $this->removedCraftsmanIDs;
    }

    /**
     * @param string[] $removedCraftsmanIDs
     */
    public function setRemovedCraftsmanIDs(array $removedCraftsmanIDs): void
    {
        $this->removedCraftsmanIDs = $removedCraftsmanIDs;
    }

    /**
     * @return ConstructionSite[]
     */
    public function getChangedConstructionSites(): array
    {
        return $this->changedConstructionSites;
    }

    /**
     * @param ConstructionSite[] $changedConstructionSites
     */
    public function setChangedConstructionSites(array $changedConstructionSites): void
    {
        $this->changedConstructionSites = $changedConstructionSites;
    }

    /**
     * @return string[]
     */
    public function getRemovedConstructionSiteIDs(): array
    {
        return $this->removedConstructionSiteIDs;
    }

    /**
     * @param string[] $removedConstructionSiteIDs
     */
    public function setRemovedConstructionSiteIDs(array $removedConstructionSiteIDs): void
    {
        $this->removedConstructionSiteIDs = $removedConstructionSiteIDs;
    }

    /**
     * @return Map[]
     */
    public function getChangedMaps(): array
    {
        return $this->changedMaps;
    }

    /**
     * @param Map[] $changedMaps
     */
    public function setChangedMaps(array $changedMaps): void
    {
        $this->changedMaps = $changedMaps;
    }

    /**
     * @return string[]
     */
    public function getRemovedMapIDs(): array
    {
        return $this->removedMapIDs;
    }

    /**
     * @param string[] $removedMapIDs
     */
    public function setRemovedMapIDs(array $removedMapIDs): void
    {
        $this->removedMapIDs = $removedMapIDs;
    }

    /**
     * @return Issue[]
     */
    public function getChangedIssues(): array
    {
        return $this->changedIssues;
    }

    /**
     * @param Issue[] $changedIssues
     */
    public function setChangedIssues(array $changedIssues): void
    {
        $this->changedIssues = $changedIssues;
    }

    /**
     * @return string[]
     */
    public function getRemovedIssueIDs(): array
    {
        return $this->removedIssueIDs;
    }

    /**
     * @param string[] $removedIssueIDs
     */
    public function setRemovedIssueIDs(array $removedIssueIDs): void
    {
        $this->removedIssueIDs = $removedIssueIDs;
    }

    public function getChangedUser(): ?User
    {
        return $this->changedUser;
    }

    public function setChangedUser(User $changedUser): void
    {
        $this->changedUser = $changedUser;
    }
}
