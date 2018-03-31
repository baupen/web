<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/*
 * Address information
 */

trait PublicAccessibleTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $publicIdentifier;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicIdentifierValidTill;

    /**
     * @return string
     */
    public function getPublicIdentifier()
    {
        return $this->publicIdentifier;
    }

    /**
     * @return \DateTime
     */
    public function getPublicIdentifierValidTill()
    {
        return $this->publicIdentifierValidTill;
    }

    /**
     * make it accessible
     *
     * @param int $validInDays
     */
    public function publish($validInDays = 30)
    {
        $this->publicIdentifier = Uuid::uuid4();
        $offset = new \DateTime();
        try {
            $offset->add(new \DateInterval("P" . $validInDays . "D"));
        } catch (\Exception $e) {
        }
        $this->publicIdentifierValidTill = $offset;
    }

    /**
     * hides it from the public
     */
    public function unPublish()
    {
        $this->publicIdentifierValidTill = null;
    }

    /**
     * @return bool
     */
    public function isAccessible()
    {
        $now = new \DateTime();
        return $now < $this->publicIdentifierValidTill;
    }
}
