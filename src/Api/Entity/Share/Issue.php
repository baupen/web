<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Share;

class Issue extends \App\Api\Entity\Base\Issue
{
    /**
     * @var \DateTime
     */
    private $registeredAt;

    /**
     * @var string
     */
    private $registrationByName;

    /**
     * @return \DateTime
     */
    public function getRegisteredAt(): \DateTime
    {
        return $this->registeredAt;
    }

    /**
     * @param \DateTime $registeredAt
     */
    public function setRegisteredAt(\DateTime $registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }

    /**
     * @return string
     */
    public function getRegistrationByName(): string
    {
        return $this->registrationByName;
    }

    /**
     * @param string $registrationByName
     */
    public function setRegistrationByName(string $registrationByName): void
    {
        $this->registrationByName = $registrationByName;
    }
}
