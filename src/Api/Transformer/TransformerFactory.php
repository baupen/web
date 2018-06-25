<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/20/18
 * Time: 9:52 AM
 */

namespace App\Api\Transformer;

class TransformerFactory
{
    /**
     * @var BuildingTransformer
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
     * @param BuildingTransformer $buildingTransformer
     * @param CraftsmanTransformer $craftsmanTransformer
     * @param MapTransformer $mapTransformer
     * @param IssueTransformer $issueTransformer
     * @param UserTransformer $userTransformer
     */
    public function __construct(BuildingTransformer $buildingTransformer, CraftsmanTransformer $craftsmanTransformer, MapTransformer $mapTransformer, IssueTransformer $issueTransformer, UserTransformer $userTransformer)
    {
        $this->buildingTransformer = $buildingTransformer;
        $this->craftsmanTransformer = $craftsmanTransformer;
        $this->mapTransformer = $mapTransformer;
        $this->issueTransformer = $issueTransformer;
        $this->userTransformer = $userTransformer;
    }

    /**
     * @return BuildingTransformer
     */
    public function getBuildingTransformer(): BuildingTransformer
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
