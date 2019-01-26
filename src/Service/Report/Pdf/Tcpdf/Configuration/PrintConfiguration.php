<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Tcpdf\Configuration;

class PrintConfiguration extends DrawConfiguration
{
    const ALIGNMENT_LEFT = 'L';
    const ALIGNMENT_RIGHT = 'R';

    const FONT_FAMILY_OPEN_SANS = 'opensans';

    /**
     * @var bool
     */
    private $bold = false;

    /**
     * @var string
     */
    private $alignment = self::ALIGNMENT_LEFT;

    /**
     * @var int[]
     */
    private $textHex = [0, 0, 0];

    /**
     * @var float
     */
    private $fontSize = 8;

    /**
     * @var float
     */
    private $width;

    /**
     * @var string
     */
    private $fontFamily = self::FONT_FAMILY_OPEN_SANS;

    /**
     * @return bool
     */
    public function getIsBold(): bool
    {
        return $this->bold;
    }

    /**
     * @return string
     */
    public function getAlignment(): string
    {
        return $this->alignment;
    }

    /**
     * @param array $config
     *
     * @throws \Exception
     */
    public function setConfiguration(array $config)
    {
        parent::setConfiguration($config);

        if (isset($config['bold'])) {
            $this->ensureBoolean($config, 'bold');
            $this->bold = $config['bold'];
        }

        if (isset($config['alignment'])) {
            $this->ensureAlignment($config, 'alignment');
            $this->alignment = $config['alignment'];
        }

        if (isset($config['fontSize'])) {
            $this->ensureFloatNumber($config, 'fontSize');
            $this->fontSize = $config['fontSize'];
        }

        if (isset($config['fontFamily'])) {
            $this->ensureFontFamily($config, 'fontFamily');
            $this->fontFamily = $config['fontFamily'];
        }

        if (isset($config['textColor'])) {
            $this->ensureHex($config, 'textColor');
            $hexColor = mb_substr($config['textColor'], 1);
            $this->textHex = [hexdec(mb_substr($hexColor, 0, 2)), hexdec(mb_substr($hexColor, 2, 2)), hexdec(mb_substr($hexColor, 4, 2))];
        }
    }

    /**
     * @param array $config
     * @param string $key
     *
     * @throws \Exception
     */
    private function ensureBoolean(array $config, string $key)
    {
        if (!\is_bool($config[$key])) {
            throw new \Exception($key . ' config must be a bool');
        }
    }

    /**
     * @param array $config
     * @param string $key
     *
     * @throws \Exception
     */
    private function ensureFloatNumber(array $config, string $key)
    {
        $value = $config[$key];
        if (!\is_float($value) && !\is_int($value) && !\is_float($value)) {
            throw new \Exception($key . ' config must be a float');
        }
    }

    /**
     * @param array $config
     * @param string $key
     *
     * @throws \Exception
     */
    private function ensureAlignment(array $config, string $key)
    {
        $alignValues = [self::ALIGNMENT_LEFT, self::ALIGNMENT_RIGHT];
        if (!\in_array($config[$key], $alignValues, true)) {
            throw new \Exception($key . ' config must one of ' . implode(',', $alignValues));
        }
    }

    /**
     * @param array $config
     * @param string $key
     *
     * @throws \Exception
     */
    private function ensureFontFamily(array $config, string $key)
    {
        $fontFamilyValues = [self::FONT_FAMILY_OPEN_SANS];
        if (!\in_array($config[$key], $fontFamilyValues, true)) {
            throw new \Exception($key . ' config must one of ' . implode(',', $fontFamilyValues));
        }
    }

    /**
     * @param \App\Service\Report\Pdf\Tcpdf\Pdf $pdf
     */
    public function apply(\App\Service\Report\Pdf\Tcpdf\Pdf $pdf)
    {
        parent::apply($pdf);

        $pdf->SetFont($this->fontFamily, $this->bold ? 'b' : '', $this->fontSize);
        $pdf->SetTextColor(...$this->textHex);
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }
}
