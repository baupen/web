<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filters\RequiredSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\FileTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A MapFile is the actual .pdf file connected to a logical map.
 *
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"map-file-read"}}
 * )
 * @ApiFilter(RequiredSearchFilter::class, properties={"constructionSite"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class MapFile extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    /**
     * @var ConstructionSite
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionSite")
     */
    private $constructionSite;

    /**
     * @var Map|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Map", mappedBy="file")
     */
    private $map;

    /**
     * @var Issue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Issue", mappedBy="mapFile")
     */
    private $issues;

    public function __construct()
    {
        $this->issues = new ArrayCollection();
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function getMap(): ?Map
    {
        return $this->map;
    }

    public function setMap(?Map $map): void
    {
        $this->map = $map;
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * @Groups({"map-file-read"})
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @Groups({"map-file-read"})
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
