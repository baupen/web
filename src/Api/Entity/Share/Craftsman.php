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

class Craftsman extends \App\Api\Entity\Base\Craftsman
{
    /**
     * @var string
     */
    private $reportUrl;

    /**
     * @return string
     */
    public function getReportUrl(): string
    {
        return $this->reportUrl;
    }

    /**
     * @param string $reportUrl
     */
    public function setReportUrl(string $reportUrl): void
    {
        $this->reportUrl = $reportUrl;
    }
}
