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

class DrawConfiguration
{
    /**
     * @var bool
     */
    private $fill = false;

    /**
     * @var int[]
     */
    private $fillHex = [0, 0, 0];

    /**
     * @var bool
     */
    private $border = false;

    /**
     * @var int[]
     */
    private $borderHex = [0, 0, 0];

    /**
     * @param array $config
     *
     * @throws \Exception
     */
    public function setConfiguration(array $config)
    {
        if (isset($config['fillColor'])) {
            $this->fill = $config['fillColor'] === false ? false : true;
            if ($this->fill) {
                $this->ensureHex($config, 'fillColor');
                $hexColor = mb_substr($config['fillColor'], 1);
                $this->fillHex = [hexdec(mb_substr($hexColor, 0, 2)), hexdec(mb_substr($hexColor, 2, 2)), hexdec(mb_substr($hexColor, 4, 2))];
            }
        }

        if (isset($config['borderColor'])) {
            $this->border = $config['borderColor'] === false ? false : true;
            if ($this->border) {
                $this->ensureHex($config, 'borderColor');
                $hexColor = mb_substr($config['borderColor'], 1);
                $this->borderHex = [hexdec(mb_substr($hexColor, 0, 2)), hexdec(mb_substr($hexColor, 2, 2)), hexdec(mb_substr($hexColor, 4, 2))];
            }
        }
    }

    /**
     * @param array $config
     * @param string $key
     *
     * @throws \Exception
     */
    protected function ensureHex(array $config, string $key)
    {
        $value = $config[$key];
        if (!preg_match('/^#([a-f0-9]){6}$/', $value)) {
            throw new \Exception($key . ' config must be a hex value like #000000');
        }
    }

    /**
     * @return bool
     */
    public function getFill(): bool
    {
        return $this->fill;
    }

    /**
     * @return bool
     */
    public function getBorder(): bool
    {
        return $this->border;
    }

    /**
     * @param \App\Service\Report\Pdf\Tcpdf\Pdf $pdf
     */
    public function apply(\App\Service\Report\Pdf\Tcpdf\Pdf $pdf)
    {
        $pdf->SetFillColor(...$this->fillHex);
        $pdf->SetDrawColor(...$this->borderHex);
    }
}
