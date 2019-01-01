<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Configuration;

class TableColumn
{
    const SIZING_BY_HEADER = 'sizing_by_header';
    const SIZING_EXPAND = 'sizing_expand';

    /**
     * @var string
     */
    private $sizing;

    /**
     * @param string $sizing
     */
    public function __construct(string $sizing = self::SIZING_EXPAND)
    {
        $this->sizing = $sizing;
    }

    /**
     * @return mixed
     */
    public function getSizing()
    {
        return $this->sizing;
    }
}
