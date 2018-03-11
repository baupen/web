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
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\ThingTrait;
use App\Enum\EmailType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Email is a sent email to the specified receivers.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EmailRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Craftsman extends BaseEntity
{
    use IdTrait;
    use ThingTrait;
    use CommunicationTrait;


    /**
     * @var Marker[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Marker", mappedBy="craftsman")
     */
    private $markers;

    /**
     * Craftsman constructor.
     */
    public function __construct()
    {
        $this->markers = new ArrayCollection();
    }

    /**
     * @return Marker[]|ArrayCollection
     */
    public function getMarkers()
    {
        return $this->markers;
    }
}
