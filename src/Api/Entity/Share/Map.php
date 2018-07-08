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
     * @var Issue[]
     */
    private $issues;

    /**
     * @var string|null
     */
    private $imageShareView;

    /**
     * @var string|null
     */
    private $imageFull;

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

    /**
     * @return null|string
     */
    public function getImageShareView(): ?string
    {
        return $this->imageShareView;
    }

    /**
     * @param null|string $imageShareView
     */
    public function setImageShareView(?string $imageShareView): void
    {
        $this->imageShareView = $imageShareView;
    }

    /**
     * @return null|string
     */
    public function getImageFull(): ?string
    {
        return $this->imageFull;
    }

    /**
     * @param null|string $imageFull
     */
    public function setImageFull(?string $imageFull): void
    {
        $this->imageFull = $imageFull;
    }
}
