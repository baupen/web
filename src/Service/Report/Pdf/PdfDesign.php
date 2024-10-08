<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf;

class PdfDesign
{
    private string $fontFamily = 'opensans';

    /**
     * @var int[]
     */
    private array $textColor = [37, 40, 32];

    /**
     * @var int[]
     */
    private array $secondaryTextColor = [68, 73, 58];

    /**
     * @var int[]
     */
    private array $darkBackground = [200, 200, 200];

    /**
     * @var int[]
     */
    private array $lightBackground = [230, 230, 230];

    /**
     * @var int[]
     */
    private array $lighterBackground = [240, 240, 240];

    /**
     * @return string[]
     */
    public function getDefaultFontFamily(): array
    {
        return [$this->fontFamily];
    }

    /**
     * @return string[]
     */
    public function getEmphasisFontFamily(): array
    {
        return [$this->fontFamily, 'b'];
    }

    /**
     * @return int[]
     */
    public function getTextColor(): array
    {
        return $this->textColor;
    }

    /**
     * @return int[]
     */
    public function getDarkBackground(): array
    {
        return $this->darkBackground;
    }

    /**
     * @return int[]
     */
    public function getLightBackground(): array
    {
        return $this->lightBackground;
    }

    /**
     * @return int[]
     */
    public function getLighterBackground(): array
    {
        return $this->lighterBackground;
    }

    /**
     * @return int[]
     */
    public function getWhiteBackground(): array
    {
        return [255, 255, 255];
    }

    /**
     * @return int[]
     */
    public function getSecondaryTextColor(): array
    {
        return $this->secondaryTextColor;
    }

    /**
     * @param int[] $secondaryTextColor
     */
    public function setSecondaryTextColor(array $secondaryTextColor): void
    {
        $this->secondaryTextColor = $secondaryTextColor;
    }
}
