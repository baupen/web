<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Request;

use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\Base\AuthenticatedRequest;
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
    private $constructionSites;

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
    public function getConstructionSites(): array
    {
        return $this->constructionSites;
    }

    /**
     * @param ObjectMeta[] $constructionSites
     */
    public function setConstructionSites(array $constructionSites): void
    {
        $this->constructionSites = $constructionSites;
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
