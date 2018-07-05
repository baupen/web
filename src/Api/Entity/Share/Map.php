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

class Map extends \App\Api\Entity\Base\Map
{
    /**
     * @var string
     */
    private $imageFilePath;

    /**
     * @var Issue[]
     */
    private $issues;

    /**
     * @return string|null
     */
    public function getImageFilePath(): ?string
    {
        return $this->imageFilePath;
    }

    /**
     * @param string $imageFilePath
     */
    public function setImageFilePath(string $imageFilePath): void
    {
        $this->imageFilePath = $imageFilePath;
    }

    /**
     * @return Issue[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /**
     * @param Issue[] $issues
     */
    public function setIssues(array $issues): void
    {
        $this->issues = $issues;
    }
}
