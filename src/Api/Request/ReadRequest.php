<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Request;


use App\Api\Entity\ObjectMeta;
use App\Api\Request\Base\AuthenticatedRequest;
use Symfony\Component\Validator\Constraints as Assert;

class ReadRequest extends AuthenticatedRequest
{
    /**
     * @var ObjectMeta
     *
     * @Assert\NotNull()
     */
    private $user;

    /**
     * @var ObjectMeta[]
     *
     * @Assert\NotNull()
     */
    private $craftsmen;

    /**
     * @var ObjectMeta[]
     *
     * @Assert\NotNull()
     */
    private $buildings;

    /**
     * @var ObjectMeta[]
     *
     * @Assert\NotNull()
     */
    private $maps;

    /**
     * @var ObjectMeta[]
     *
     * @Assert\NotNull()
     */
    private $issues;

    /**
     * @return ObjectMeta
     */
    public function getUser(): ObjectMeta
    {
        return $this->user;
    }

    /**
     * @param ObjectMeta $user
     */
    public function setUser(ObjectMeta $user): void
    {
        $this->user = $user;
    }

    /**
     * @return ObjectMeta[]
     */
    public function getCraftsmen(): array
    {
        return $this->craftsmen;
    }

    /**
     * @param ObjectMeta[] $craftsmen
     */
    public function setCraftsmen(array $craftsmen): void
    {
        $this->craftsmen = $craftsmen;
    }

    /**
     * @return ObjectMeta[]
     */
    public function getBuildings(): array
    {
        return $this->buildings;
    }

    /**
     * @param ObjectMeta[] $buildings
     */
    public function setBuildings(array $buildings): void
    {
        $this->buildings = $buildings;
    }

    /**
     * @return ObjectMeta[]
     */
    public function getMaps(): array
    {
        return $this->maps;
    }

    /**
     * @param ObjectMeta[] $maps
     */
    public function setMaps(array $maps): void
    {
        $this->maps = $maps;
    }

    /**
     * @return ObjectMeta[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /**
     * @param ObjectMeta[] $issues
     */
    public function setIssues(array $issues): void
    {
        $this->issues = $issues;
    }
}