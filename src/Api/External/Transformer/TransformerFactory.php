<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Transformer;

class TransformerFactory
{
    /**
     * @var ConstructionSiteTransformer
     */
    private $buildingTransformer;

    /**
     * @var CraftsmanTransformer
     */
    private $craftsmanTransformer;

    /**
     * @var MapTransformer
     */
    private $mapTransformer;

    /**
     * @var IssueTransformer
     */
    private $issueTransformer;

    /**
     * @var UserTransformer
     */
    private $userTransformer;

    /**
     * TransformerFactory constructor.
     *
     * @param ConstructionSiteTransformer $buildingTransformer
     * @param CraftsmanTransformer $craftsmanTransformer
     * @param MapTransformer $mapTransformer
     * @param IssueTransformer $issueTransformer
     * @param UserTransformer $userTransformer
     */
    public function __construct(ConstructionSiteTransformer $buildingTransformer, CraftsmanTransformer $craftsmanTransformer, MapTransformer $mapTransformer, IssueTransformer $issueTransformer, UserTransformer $userTransformer)
    {
        $this->buildingTransformer = $buildingTransformer;
        $this->craftsmanTransformer = $craftsmanTransformer;
        $this->mapTransformer = $mapTransformer;
        $this->issueTransformer = $issueTransformer;
        $this->userTransformer = $userTransformer;
    }

    /**
     * @return ConstructionSiteTransformer
     */
    public function getBuildingTransformer(): ConstructionSiteTransformer
    {
        return $this->buildingTransformer;
    }

    /**
     * @return CraftsmanTransformer
     */
    public function getCraftsmanTransformer(): CraftsmanTransformer
    {
        return $this->craftsmanTransformer;
    }

    /**
     * @return MapTransformer
     */
    public function getMapTransformer(): MapTransformer
    {
        return $this->mapTransformer;
    }

    /**
     * @return IssueTransformer
     */
    public function getIssueTransformer(): IssueTransformer
    {
        return $this->issueTransformer;
    }

    /**
     * @return UserTransformer
     */
    public function getUserTransformer(): UserTransformer
    {
        return $this->userTransformer;
    }
}
