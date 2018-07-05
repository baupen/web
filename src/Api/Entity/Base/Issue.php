<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Base;

class Issue extends BaseEntity
{
    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $imageFilePath;

    /**
     * @var \DateTime|null
     */
    private $responseLimit;

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return null|string
     */
    public function getImageFilePath(): ?string
    {
        return $this->imageFilePath;
    }

    /**
     * @param null|string $imageFilePath
     */
    public function setImageFilePath(?string $imageFilePath): void
    {
        $this->imageFilePath = $imageFilePath;
    }

    /**
     * @return \DateTime|null
     */
    public function getResponseLimit(): ?\DateTime
    {
        return $this->responseLimit;
    }

    /**
     * @param \DateTime|null $responseLimit
     */
    public function setResponseLimit(?\DateTime $responseLimit): void
    {
        $this->responseLimit = $responseLimit;
    }
}
