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

class PublicMap extends Map
{
    /**
     * @var string|null
     */
    private $imageShareView;

    /**
     * @var string|null
     */
    private $imageFull;

    /**
     * @var string|null
     */
    private $context;

    public function getImageShareView(): ?string
    {
        return $this->imageShareView;
    }

    public function setImageShareView(?string $imageShareView): void
    {
        $this->imageShareView = $imageShareView;
    }

    public function getImageFull(): ?string
    {
        return $this->imageFull;
    }

    public function setImageFull(?string $imageFull): void
    {
        $this->imageFull = $imageFull;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): void
    {
        $this->context = $context;
    }
}
