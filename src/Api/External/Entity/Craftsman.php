<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Entity;

use App\Api\External\Entity\Base\BaseEntity;

class Craftsman extends BaseEntity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $trade;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTrade(): string
    {
        return $this->trade;
    }

    /**
     * @param string $trade
     */
    public function setTrade(string $trade): void
    {
        $this->trade = $trade;
    }
}
