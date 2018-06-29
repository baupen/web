<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Foyer;

class Issue extends \App\Api\Entity\Base\Issue
{
    /**
     * @var \DateTime
     */
    private $uploadedAt;

    /**
     * @var string
     */
    private $uploadByName;

    /**
     * @return \DateTime
     */
    public function getUploadedAt(): \DateTime
    {
        return $this->uploadedAt;
    }

    /**
     * @param \DateTime $uploadedAt
     */
    public function setUploadedAt(\DateTime $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
    }

    /**
     * @return string
     */
    public function getUploadByName(): string
    {
        return $this->uploadByName;
    }

    /**
     * @param string $uploadByName
     */
    public function setUploadByName(string $uploadByName): void
    {
        $this->uploadByName = $uploadByName;
    }
}
