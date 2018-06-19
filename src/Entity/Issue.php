<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * An issue is something created by the construction manager to inform the craftsman of it
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Issue extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $number;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isMarked;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $wasAddedWithClient;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $imageFilename;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $uploadedAt;

    /**
     * @var ConstructionManager
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $uploadBy;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registeredAt;

    /**
     * @var ConstructionManager|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $registrationBy;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $respondedAt;

    /**
     * @var Craftsman|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman")
     */
    private $responseBy;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reviewedAt;

    /**
     * @var ConstructionManager|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $reviewBy;

    /**
     * @var double|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $positionX;

    /**
     * @var double|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $positionY;

    /**
     * @var double|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $positionZoomScale;

    /**
     * @var Craftsman|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman", inversedBy="issues")
     */
    private $craftsman;
}
